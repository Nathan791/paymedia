import 'package:flutter/material.dart';
import 'onboarding_services.dart';
import 'payment_sync_manager.dart';
import 'models.dart';

void main() => runApp(const PayMediaApp());

class PayMediaApp extends StatefulWidget {
  const PayMediaApp({super.key});

  @override
  State<PayMediaApp> createState() => _PayMediaAppState();
}

class _PayMediaAppState extends State<PayMediaApp> {
  final PaymentSyncManager _syncManager = PaymentSyncManager();

  @override
  void initState() {
    super.initState();
    //Fire up the hardware network listener loops instantly on app launch
    _syncManager.startNetworkMonitoringService();
  }

  @override
  void dispose() {
    _syncManager.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => MaterialApp(
        debugShowCheckedModeBanner: false,
        theme: ThemeData(useMaterial3: true, colorSchemeSeed: Colors.indigo),
        home: const PayMediaDashboard(),
      );
}

class PayMediaDashboard extends StatefulWidget {
  const PayMediaDashboard({super.key});
  @override
  State<PayMediaDashboard> createState() => _PayMediaDashboardState();
}

class _PayMediaDashboardState extends State<PayMediaDashboard> {
  final OnboardingService _onboarding = OnboardingService();
  final TextEditingController _codeController = TextEditingController();

  UserProfile? _currentUser;
  double _balance = 10.00; // Mock initial balance for testing preview

  @override
  void initState() {
    super.initState();
    _onboarding.initializeAppTrack().then((profile) {
      setState(() {
        _currentUser = profile;
      });
    });
  }

  void _processShortcode() {
    final code = _codeController.text;
    if (code.length < 3) return;

    if (_balance >= 0.25) {
      setState(() {
        _balance -= 0.25; // Execute micro-deduction local loop
      });
      _codeController.clear();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
            content: Text('Code $code Validated! Article unlocked (-\$0.25)')),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Insufficient balance! Please top up.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('💡 PayMedia Edge Reader')),
      body: _currentUser == null
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Card(
                    color: Colors.indigo.shade50,
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      key: ValueKey(_balance),
                      child: Column(
                        children: [
                          Text('Prepaid Wallet Balance',
                              style: TextStyle(color: Colors.indigo.shade900)),
                          const SizedBox(height: 8),
                          Text('\$${_balance.toStringAsFixed(2)}',
                              style: const TextStyle(
                                  fontSize: 32, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 40),
                  TextField(
                    controller: _codeController,
                    keyboardType: TextInputType.number,
                    maxLength: 4,
                    decoration: const InputDecoration(
                      labelText: 'Enter Print Shortcode',
                      hintText: 'e.g., 402',
                      border: OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _processShortcode,
                    style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.all(16)),
                    child: const Text('Unlock Headline'),
                  ),
                ],
              ),
            ),
    );
  }
}
