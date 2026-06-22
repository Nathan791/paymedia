<?php
session_start();
$error = "";
$success = "";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $mysqli = new mysqli("localhost", "root", "", "paymedia");
    $mysqli->set_charset("utf8mb4");
}catch (Exception $e) {
    error_log($e->getMessage());
    exit('Error connecting to database'); // Display a generic error message to the user
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function isValidImage($file_path) {
    if (!file_exists($file_path)) return false;
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    return in_array($mime, $allowed);
}

function downloadImage($url, $dest) {
    $options = ['http' => ['timeout' => 5]]; // Prevent hanging
    $context = stream_context_create($options);
    $content = @file_get_contents($url, false, $context);
    if (!$content || strlen($content) > 5000000) return false; // 5MB Limit
    file_put_contents($dest, $content);
    return isValidImage($dest);
}

/* ==========================
   FORM PROCESSING
========================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch.";
    } else {
        $title = trim($_POST['title'] ?? "");
        $name  = trim($_POST['name'] ?? "");
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
         

        if (!$title || !$name || $price === false ) {
            $error = "Please provide valid product details.";
        } else {
            $db->begin_transaction();
            try {
                // Insert Product
                $stmt = $db->prepare("INSERT INTO products (title, name, price, status) VALUES (?, ?, ?, 'active')");
                $stmt->bind_param("ssdi", $title, $name, $price, $stock);
                $stmt->execute();
                $productId = $db->insert_id;

                $dir = $upload_base_dir . "product_$productId/";
                if (!is_dir($dir)) mkdir($dir, 0755, true);

                $imgStmt = $db->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");

                // Handle Local Uploads
                if (!empty($_FILES['images']['tmp_name'][0])) {
                    foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
                        if ($_FILES['images']['error'][$k] === UPLOAD_ERR_OK && isValidImage($tmp)) {
                            $ext = pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION);
                            $path = $dir . bin2hex(random_bytes(8)) . "." . $ext;
                            if (move_uploaded_file($tmp, $path)) {
                                $imgStmt->bind_param("is", $productId, $path);
                                $imgStmt->execute();
                            }
                        }
                    }
                }

                // Handle URL Uploads
                $urls = array_filter(array_map('trim', explode("\n", $_POST['image_urls'] ?? "")));
                foreach ($urls as $url) {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        $path = $dir . bin2hex(random_bytes(8)) . ".jpg";
                        if (downloadImage($url, $path)) {
                            $imgStmt->bind_param("is", $productId, $path);
                            $imgStmt->execute();
                        }
                    }
                }

                $db->commit();
                $success = "Product created successfully!";
                // Refresh token to prevent double submission
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
            } catch (Exception $e) {
                $db->rollback();
                $error = "Error saving product: " . $e->getMessage();
            }
        }
    }
}
?>
<!Doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PayMedia - Add Product</title>
        <link rel="stylesheet" href="style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    </head>
    <body>
        <div class="container mt-5">
            <h2>Add New Product</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="mb-3">
                    <label for="title" class="form-label">Product Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                </div>
                <div class="mb-3">
                    <label for="images" class="form-label">Upload Images</label>
                    <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                </div>
                <div class="mb-3">
                    <label for="image_urls" class="form-label">Image URLs (one per line)</label>
                    <textarea class="form-control" id="image_urls" name="image_urls" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>