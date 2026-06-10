import 'package:shared_preferences/shared_preferences.dart';
import 'package:uuid/uuid.dart';
import 'models.dart';

class OnboardingService {
  static const String _keyIsFirstRun = "is_first_run";

  Future<UserProfile> initializeAppTrack() async {
    final prefs = await SharedPreferences.getInstance();
    final isFirstRun = prefs.getBool(_keyIsFirstRun) ?? true;

    if (isFirstRun) {
      // Generate a brand new anonymous guest profile instantly
      String newUserId = const Uuid().v4();
      String simulatedDeviceId = const Uuid().v4(); // In real production, read hardware identifiers

      // Save to local device memory flags
      await prefs.setBool(_keyIsFirstRun, false);
      await prefs.setString("user_id", newUserId);
      await prefs.setString("device_id", simulatedDeviceId);

      return UserProfile(
        userId: newUserId,
        deviceId: simulatedDeviceId,
        accountType: AccountType.guest,
      );
    } else {
      // Return existing profile properties
      return UserProfile(
        userId: prefs.getString("user_id") ?? const Uuid().v4(),
        deviceId: prefs.getString("device_id") ?? "unknown_device",
        accountType: AccountType.guest,
      );
    }
  }
}

