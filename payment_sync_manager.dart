import 'dart:async';
import 'dart:convert';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'database_helper.dart';

class PaymentSyncManager {
  final DatabaseHelper _dbHelper = DatabaseHelper.instance;
  late StreamSubscription<List<ConnectivityResult>> _connectivitySubscription;

  /// Starts watching the device radio signal channels in real-time
  void startNetworkMonitoringService() {
    _connectivitySubscription = Connectivity()
        .onConnectivityChanged
        .listen((List<ConnectivityResult> results) async {
      // Check if any active network interfaces are alive (Wi-Fi or Mobile Data)
      bool hasSignal = results.contains(ConnectivityResult.wifi) ||
          results.contains(ConnectivityResult.mobile);

      if (hasSignal) {
        debugPrint(
            "💡 Connection Found: Flushing offline logs to cloud PostgreSQL server...");
        await flushCachedTransactionsUpstream();
      } else {
        debugPrint(
            "🚇 Device Submerged Offline: Internal transactions will queue in secure storage.");
      }
    });
  }

  /// Iterates through pending rows and synchronizes them with the cloud
  Future<void> flushCachedTransactionsUpstream() async {
    final db = await _dbHelper.database;

    // 1. Fetch all local transactions flagged as un-synchronized
    final List<Map<String, dynamic>> pendingRows = await db.query(
      'pending_transactions',
      where: 'sync_status = ?',
      whereArgs: ['PENDING_SYNC'],
    );

    if (pendingRows.isEmpty) return;

    debugPrint("Sending ${pendingRows.length} transactions upstream...");

    // 2. Process our secure outbound upload payload batch block
    try {
      final response = await http.post(
        Uri.parse('https://api.paymedia.com/api/v1/sync-ledger'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'walletId': 'sample-user-wallet-uuid', // Map dynamically in real run
          'transactions': pendingRows,
        }),
      );

      if (response.statusCode == 200) {
        // 3. Update local data storage queue rows upon verified server confirmation
        await db.update(
          'pending_transactions',
          {'sync_status': 'CONFIRMED'},
          where: 'sync_status = ?',
          whereArgs: ['PENDING_SYNC'],
        );
        debugPrint(
            "🎉 Cloud ledger perfectly synchronized with master PostgreSQL servers.");
      } else {
        debugPrint("Server synchronization failed with status: ${response.statusCode}");
      }
    } catch (e) {
      debugPrint(
          "Network transmission failed. Will retry automatically upon next radio connection change.");
    }
  }

  /// Terminate system background listening ports when the app context closes
  void dispose() {
    _connectivitySubscription.cancel();
  }
}
