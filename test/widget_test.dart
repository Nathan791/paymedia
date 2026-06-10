import 'package:flutter_test/flutter_test.dart';
import 'package:paymedia/main.dart';

void main() {
  testWidgets('App starts and shows dashboard', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const PayMediaApp());

    // Verify that the loading indicator or the app title is present.
    // Since initializeAppTrack is called in initState, it might be loading initially.
    expect(find.text('💡 PayMedia Edge Reader'), findsOneWidget);
  });
}
