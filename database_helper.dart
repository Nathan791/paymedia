import 'package:path/path.dart';
import 'package:sqflite_sqlcipher/sqflite.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:uuid/uuid.dart';

class DatabaseHelper {
  static final DatabaseHelper instance = DatabaseHelper._init();
  static Database? _database;

  // Instantiate the hardware-backed secure storage engine
  final _secureStorage = const FlutterSecureStorage();
  static const _keyName = "paymedia_database_key";

  DatabaseHelper._init();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDB('paymedia_local.db');
    return _database!;
  }

  /// Fetches the existing key from hardware storage or creates a brand new one
  Future<String> _getOrCreateDatabaseKey() async {
    // 1. Attempt to read an existing cryptographic key out of the Keychain/Keystore
    String? existingKey = await _secureStorage.read(key: _keyName);

    if (existingKey != null) {
      return existingKey;
    }

    // 2. If it returns null, this is a brand new app installation.
    // Generate a long, random, non-guessable string to act as the database password.
    String newKey = "${const Uuid().v4()}_${const Uuid().v4()}";

    // 3. Commit the new password string directly into the device's hardware vault
    await _secureStorage.write(
      key: _keyName,
      value: newKey,
      // Enforces iOS Keychain backup options
      iOptions: const IOSOptions(accessibility: KeychainAccessibility.first_unlock),
      // Enforces Android Keystore hardware encryption parameters
      aOptions: const AndroidOptions(),
    );

    return newKey;
  }

  Future<Database> _initDB(String filePath) async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, filePath);

    // Fetch the hardware-protected key dynamically at boot time
    final databasePassword = await _getOrCreateDatabaseKey();

    return await openDatabase(
      path,
      version: 1,
      password: databasePassword, // Passed to SQLCipher to decrypt tables in-memory
      onCreate: _createDB,
    );
  }

  Future _createDB(Database db, int version) async {
    await db.execute('''
      CREATE TABLE local_wallets (
        wallet_id TEXT PRIMARY KEY,
        user_id TEXT NOT NULL,
        current_balance REAL NOT NULL,
        local_signature_token TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE local_access_passes (
        pass_id TEXT PRIMARY KEY,
        scope_type TEXT NOT NULL,
        scope_target_id TEXT NOT NULL,
        expires_at TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE pending_transactions (
        transaction_id TEXT PRIMARY KEY,
        amount REAL NOT NULL,
        type TEXT NOT NULL,
        sync_status TEXT NOT NULL
      )
    ''');
  }
}