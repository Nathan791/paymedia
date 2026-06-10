enum AccountType { guest, registered }

enum SyncStatus { pendingSync, confirmed, failed }

class UserProfile {
  final String userId;
  final String deviceId;
  final AccountType accountType;

  UserProfile({
    required this.userId,
    required this.deviceId,
    required this.accountType,
  });
}

class Wallet {
  final String walletId;
  final String userId;
  final double currentBalance;
  String localSignatureToken;

  Wallet({
    required this.walletId,
    required this.userId,
    required this.currentBalance,
    required this.localSignatureToken,
  });
}
