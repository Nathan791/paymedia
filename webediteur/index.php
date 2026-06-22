<?php
session_start();

if (!isset($_SESSION["email"])) {
    header("Location: /webediteur/login.php");
    exit();
}

$userName = htmlspecialchars($_SESSION["name"] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Paymedia</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <style>
       :root {
    --primary-color: #7380ec;
    --color-danger: #ff7782;
    --color-success: #41f1b6;
    --color-warning: #ffbb55;
    --color-white: #fff;
    --color-info-dark: #7d8da1;
    --color-info-light: #dce1eb;
    --color-dark: #363949;
    --color-light: rgba(132, 139, 200, 0.18);
    --color-primary-variant: #111e88;
    --color-dark-variant: #677483;
    --background-color: #f6f6f9;

    --card-border-radius: 2rem;
    --border-radius-1: 0.4rem;
    --border-radius-2: 0.8rem;
    --border-radius-3: 1.2rem;

    --card-padding: 1.8rem;
    --padding-1: 1.2rem;
    --box-shadow: 0 2rem 3rem var(--color-light);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

.container {
    display: grid;
    width: 96%;
    margin: 0 auto;
    gap: 1.8rem;
    grid-template-columns: 14rem auto 23rem;
}

a {
    color: var(--color-dark);
}

img {
    display: block;
    width: 100%;
}

h1 { font-weight: 800; font-size: 1.8rem; }
h2 { font-size: 1.4rem; }
h3 { font-size: 0.87rem; }
h4 { font-size: 0.8rem; }
h5 { font-size: 0.77rem; }
small { font-size: 0.75rem; }

.profile-photo {
    width: 2.8rem;
    height: 2.8rem;
    border-radius: 50%;
    overflow: hidden;
}

.text-muted { color: var(--color-info-dark); }
p { color: var(--color-dark-variant); }
b { color: var(--color-dark); }

.primary { color: var(--primary-color); }
.danger { color: var(--color-danger); }
.success { color: var(--color-success); }
.warning { color: var(--color-warning); }

/* ================== ASIDE / SIDEBAR ================== */
aside {
    height: 100vh;
}

aside .top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1.4rem;
}

aside .logo {
    display: flex;
    gap: 0.8rem;
}

aside .logo img {
    width: 2rem;
    height: 2rem;
}

aside .close {
    display: none;
}

aside .sidebar {
    display: flex;
    flex-direction: column;
    height: 86vh;
    position: relative;
    top: 3rem;
}

aside h3 {
    font-weight: 500;
}

aside .sidebar a {
    display: flex;
    color: var(--color-info-dark);
    margin-left: 2rem;
    gap: 1rem;
    align-items: center;
    position: relative;
    height: 3.7rem;
    transition: all 300ms ease;
}

aside .sidebar a span {
    font-size: 1.6rem;
    transition: all 300ms ease;
}

aside .sidebar a:last-child {
    position: absolute;
    bottom: 2rem;
    width: 100%;
}

aside .sidebar a.active {
    background: var(--color-light);
    color: var(--primary-color);
    margin-left: 0;
}

aside .sidebar a.active:before {
    content: "";
    width: 6px;
    height: 100%;
    background: var(--primary-color);
}

aside .sidebar a.active span {
    color: var(--primary-color);
    margin-left: calc(1rem - 6px);
}

aside .sidebar a:hover {
    color: var(--primary-color);
}

aside .sidebar a:hover span {
    margin-left: 1rem;
}

aside .sidebar .message-count {
    background: var(--color-danger);
    color: var(--color-white);
    padding: 2px 10px;
    font-size: 11px;
    border-radius: var(--border-radius-1);
}

/* ================== MAIN CONTENT ================== */
main {
    margin-top: 1.4rem;
}

main .date {
    display: inline-block;
    background: var(--color-light);
    border-radius: var(--border-radius-1);
    margin-top: 1rem;
    padding: 0.5rem 1.6rem;
}

main .date input[type="date"] {
    background: transparent;
    color: var(--color-dark);
}

main .insights {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.6rem;
}

main .insights > div {
    background: var(--color-white);
    padding: var(--card-padding);
    border-radius: var(--card-border-radius);
    margin-top: 1rem;
    box-shadow: var(--box-shadow);
    transition: all 300ms ease;
}

main .insights > div:hover {
    box-shadow: none;
}

main .insights > div span {
    background: var(--primary-color);
    padding: 0.5rem;
    border-radius: 50%;
    color: var(--color-white);
    font-size: 2rem;
}

main .insights > div.expenses span { background: var(--color-danger); }
main .insights > div.income span { background: var(--color-success); }

main .insights > div .middle {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

main .insights h3 {
    margin: 1rem 0 0.6rem;
    font-size: 1rem;
}

/* SVG PROGRESS BARS */
main .insights .progress {
    position: relative;
    width: 92px;
    height: 92px;
    border-radius: 50%;
}

main .insights svg {
    width: 7rem;
    height: 7rem;
}

main .insights svg circle {
    fill: none;
    stroke: var(--primary-color);
    stroke-width: 14;
    stroke-linecap: round;
    transform: translate(5px, 5px);
    stroke-dasharray: 226;
    stroke-dashoffset: 226;
}

main .insights .sales svg circle { stroke-dashoffset: 42; } 
main .insights .expenses svg circle { stroke-dashoffset: 86; } 
main .insights .income svg circle { stroke-dashoffset: 126; } 

main .insights .progress .number {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* RECENT ORDERS TABLE */
main .recent-orders {
    margin-top: 2rem;
}

main .recent-orders h2 {
    margin-bottom: 0.8rem;
}

main .recent-orders table {
    background: var(--color-white);
    width: 100%;
    border-radius: var(--card-border-radius);
    padding: var(--card-padding);
    text-align: center;
    box-shadow: var(--box-shadow);
    transition: all 300ms ease;
}

main .recent-orders table:hover {
    box-shadow: none;
}

main table tbody td {
    height: 2.8rem;
    border-bottom: 1px solid var(--color-light);
    color: var(--color-dark-variant);
}

main table tbody tr:last-child td {
    border: none;
}

main .recent-orders a {
    text-align: center;
    display: block;
    margin: 1rem auto;
    color: var(--primary-color);
}

/* ================== RIGHT PANEL ================== */
.right {
    margin-top: 1.4rem;
}

.right .top {
    display: flex;
    justify-content: end;
    gap: 2rem;
}

.right .top button {
    display: none;
}

.right .theme-toggler {
    background: var(--color-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 1.6rem;
    width: 4.2rem;
    cursor: pointer;
    border-radius: var(--border-radius-1);
}

.right .theme-toggler span {
    font-size: 1.2rem;
    width: 50%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.right .theme-toggler span.active {
    background: var(--primary-color);
    color: white;
    border-radius: var(--border-radius-1);
}

.right .top .profile {
    display: flex;
    gap: 2rem;
    text-align: right;
}

/* RECENT UPDATES */
.right .recent-updates .updates {
    background: var(--color-white);
    padding: var(--card-padding);
    border-radius: var(--card-border-radius);
    box-shadow: var(--box-shadow);
    transition: all 300ms ease;
}

.right .recent-updates .updates .update {
    display: grid;
    grid-template-columns: 2.6rem auto;
    gap: 1rem;
    margin-bottom: 1rem;
}
    </style>
</head>
<body>
        <h1>Web Paymedia</h1>
        <div class="container">
            <aside>
                <div class="top">
                <div class="logo">
                    <img src="img/logo.png" alt="Logo">
                    <h2>Pay<span class="primary">media</span></h2>
                </div>
                <div class="close" id="close-btn">
                    <span class="material-symbols-outlined">close</span>
                </div>
            </div>
                <div class="sidebar">
                    <a href="#" class="active"><span class="material-symbols-outlined">dashboard</span><h3>Dashboard</h3></a>
                    <a href="#"><span class="material-symbols-outlined">receipt_long</span><h3>Total Sales</h3></a>
                    <a href="#"><span class="material-symbols-outlined">analytics</span><h3>Analytics</h3></a>
                    <a href="#"><span class="material-symbols-outlined">preferences</span>Customers Preferences</a>
                    <a href="#"><span class="material-symbols-outlined">inventory_2</span><h3>Products</h3></a>
                    <a href="#"><span class="material-symbols-outlined">report</span><h3>Reports</h3></a>
                    <a href="#"><span class="material-symbols-outlined">settings</span><h3>Settings</h3></a>
                    <a href="#"><span class="material-symbols-outlined">add</span><h3>Add Product</h3></a>
                    <a href="#"><span class="material-symbols-outlined">logout</span><h3>Logout</h3></a>
            </aside>
        </div>
        <main>
            <h1>Dashboard</h1>
            <div class="date">
                <input type="date">
            </div>

            <div class="insights">
                <div class="sales">
                    <span class="material-symbols-outlined">analytics</span>
                    <div class="middle">
                        <div class="left"><h3>Total Sales</h3><h1>FCFA25,000</h1></div>
                    </div>
                    <small class="text-muted">Last 24 Hours</small>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                </div>
                <div class="Preferences">
                    <span class="material-symbols-outlined">preferences</span>
                    <div class="middle">
                        <div class="left"><h3>Customers Preferences</h3><h1>250</h1></div>
                    </div>
                    <small class="text-muted">Last 24 Hours</small>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="products">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <div class="middle">
                        <div class="left"><h3>Products</h3><h1>100</h1></div>
                    </div>
                    <small class="text-muted">Last 24 Hours</small>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            
        </main>
        <script src="script.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>


        