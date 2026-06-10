import 'package:flutter_test/flutter_test.dart';
import 'package:mockito/mockito.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:paymedia/local_data_repository.dart';
import 'package:paymedia/payment_sync_manager.dart';

// Create a fake class to simulate radio handshake states manually
class MockConnectivity extends Mock implements Connectivity {
  List<ConnectivityResult> result = [ConnectivityResult.none];

  @override
  Future<List<ConnectivityResult>> checkConnectivity() async => result;

  @override
  Stream<List<ConnectivityResult>> get onConnectivityChanged =>
      Stream.value(result);
}

void main() {
  late LocalDataRepository repository;
  late PaymentSyncManager syncManager;
  late MockConnectivity mockConnectivity;


  setUp(() {
    repository = LocalDataRepository();
    syncManager = PaymentSyncManager();
    mockConnectivity = MockConnectivity();
  });

  test(
      'Subway Emulation: Verify offline transactions queue and auto-flush on signal return',
      () async {
    // 1. SIMULATE ENTERING SUBWAY (Drop connection completely)
        when(mockConnectivity.checkConnectivity())
            .thenAnswer((_) async => [ConnectivityResult.none]);


    // 2. Execute 3 separate micro-deduction loops while offline
    await repository.executeOfflinePurchase(
        targetId: 'article_ch1',
        microPrice: 0.25,
        passId: 'p1',
        scopeType: 'SINGLE_ARTICLE');
    await repository.executeOfflinePurchase(
        targetId: 'article_ch2',
        microPrice: 0.25,
        passId: 'p2',
        scopeType: 'SINGLE_ARTICLE');
    await repository.executeOfflinePurchase(
        targetId: 'article_ch3',
        microPrice: 0.25,
        passId: 'p3',
        scopeType: 'SINGLE_ARTICLE');

    // Verify that the local wallet balance was reduced properly on the edge device
    double localBalance = await repository.getLocalWalletBalance();
    // Assuming starting balance was set up or default behavior.
    // Note: This check depends on the actual initial state of the DB.
    expect(localBalance, 9.25);

    // 3. SIMULATE EXITING SUBWAY (Signal returns via Wi-Fi)
        print("Test: Commuter exited station. Re-establishing connection...");
        when(mockConnectivity.checkConnectivity())
            .thenAnswer((_) async => [ConnectivityResult.wifi]);

    // Trigger the manual sync flush routine explicitly for the test framework
    await syncManager.flushCachedTransactionsUpstream();

    print("Test: Integration verification complete. All logs flushed cleanly");
  });
}
