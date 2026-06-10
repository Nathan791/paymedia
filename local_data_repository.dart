import 'package:flutter/foundation.dart';
import 'package:sqflite_sqlcipher/sqflite.dart';
import 'database_helper.dart';
import 'models.dart';

class LocalDataRepository {
  // Grab a direct handle to our encrypted DatabaseHelper singleton instance
  final DatabaseHelper _dbHelper = DatabaseHelper.instance;

  // ==========================================
  // WALLET OPERATIONS (CRUD)
  // ==========================================

  /// Instantiates a brand new local wallet row upon initial zero-friction boot
  Future<void> createLocalWallet(Wallet wallet) async {
    final db = await _dbHelper.database;

    // Use a conflict algorithm so we don't accidentally wipe data if called twice
    await db.insert(
      'local_wallets',
      {
        'wallet_id': wallet.walletId,
        'user_id': wallet.userId,
        'current_balance': wallet.currentBalance,
        'local_signature_token': wallet.localSignatureToken,
      },
      conflictAlgorithm: ConflictAlgorithm.ignore,
    );
  }

  /// Fetches the live local balance for UI rendering loops
  Future<double> getLocalWalletBalance() async {
    final db = await _dbHelper.database;
    final List<Map<String, dynamic>> maps = await db.query('local_wallets');

    if (maps.isEmpty) return 0.00;
    return maps.first['current_balance'] as double;
  }

  // ==========================================
  // ACCESS PASS OPERATIONS
  // ==========================================

  /// Inserts a newly spawned time-gated reading authorization token
  Future<void> saveAccessPass({
    required String passId,
    required String scopeType,
    required String scopeTargetId,
    required DateTime expiresAt,
  }) async {
    final db = await _dbHelper.database;

    await db.insert(
      'local_access_passes',
      {
        'pass_id': passId,
        'scope_type': scopeType,
        'scope_target_id': scopeTargetId,
        'expires_at': expiresAt.toIso8601String(), // Store as text string for SQLite compatibility
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  /// Checks the local storage parameters to see if the user has a valid active pass
  Future<bool> hasValidAccess(String targetId) async {
    final db = await _dbHelper.database;
    final currentTime = DateTime.now().toIso8601String();

    // Query both matching targets and make sure the expiration date hasn't passed
    final List<Map<String, dynamic>> result = await db.query(
      'local_access_passes',
      where: 'scope_target_id = ? AND expires_at > ?',
      whereArgs: [targetId, currentTime],
    );

    return result.isNotEmpty;
  }

  /// Executes a safe, atomic offline balance deduction and spawns an Access Pass
  Future<bool> executeOfflinePurchase({
    required String targetId,
    required double microPrice,
    required String passId,
    required String scopeType,
  }) async {
    final db = await _dbHelper.database;

    try {
      // Open an atomic transaction block wrapper
      return await db.transaction<bool>((txn) async {
        // 1. Fetch current wallet balance safely inside the transaction block
        final List<Map<String, dynamic>> wallets = await txn.query('local_wallets');
        if (wallets.isEmpty) return false;

        double balance = wallets.first['current_balance'] as double;
        String walletId = wallets.first['wallet_id'] as String;

        // 2. Enforce structural overdraft prevention gate rules
        if (balance < microPrice) {
          return false; // Insufficient funds
        }

        double newBalance = balance - microPrice;

        // 3. Deduct balance field parameters
        await txn.update(
          'local_wallets',
          {'current_balance': newBalance},
          where: 'wallet_id = ?',
          whereArgs: [walletId],
        );

        // 4. Generate the 24-hour time-gated access key block
        final expiryTime = DateTime.now().add(const Duration(hours: 24));
        await txn.insert(
          'local_access_passes',
          {
            'pass_id': passId,
            'scope_type': scopeType,
            'scope_target_id': targetId,
            'expires_at': expiryTime.toIso8601String(),
          },
        );

        return true; // Commit successful! Both actions executed perfectly in tandem.
      });
    } catch (transactionCrashException) {
      debugPrint(
          "Local Database Error: Transaction crashed, automatically rolling back states.");
      return false;
    }
  }
}
