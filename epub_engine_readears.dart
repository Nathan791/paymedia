import 'dart:convert';
import 'dart:typed_data';
import 'package:archive/archive.dart';
import 'package:crypto/crypto.dart';

class EpubEngineReadears {
  //Simulating an AES-256 decryption process from an offline environment
  Uint8List _decryptBytes(Uint8List encryptedData, String key) {
    //
    final keyBytes = utf8.encode(key);
    final hash = sha256.convert(keyBytes).bytes;


    final decrypted = Uint8List(encryptedData.length);
    for (int i = 0; i < encryptedData.length; i++) {
      decrypted[i] = encryptedData[i] ^ hash[i % hash.length];
    }
    return decrypted;
  }

  Future<String>verifyAccessAndExtractChapter({
    required Uint8List encryptedEpubBytes,
    required String encryptionKey,
    required String targetChapterPath,
    required DateTime expiredAt,
}) async {
    if (DateTime.now().isAfter(expiredAt)) {
      throw Exception(
          "Access expired: This pass has reached its expiration time");
    }
    //Decrypt the epub file
    final decryptedEpubBytes = _decryptBytes(encryptedEpubBytes, encryptionKey);
    //Extract the target chapter from the decrypted epub file
    final archive = ZipDecoder().decodeBytes(decryptedEpubBytes);
    //Find the target chapter in the extracted archive
    final ArchiveFile? chapterFile = archive.findFile(targetChapterPath);

    if (chapterFile == null) {
      throw Exception("Chapter not found in the epub file");
    }

    final Uint8List contentBytes = chapterFile.content;
    final String htmlContent = utf8.decode(contentBytes);

    return htmlContent;

  }
}