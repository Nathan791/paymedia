import 'package:flutter/material.dart';

class ReaderViewScreen extends StatelessWidget {
  final String htmlDataStream;
  final String contentTitle;

  const ReaderViewScreen({
    super.key,
    required this.htmlDataStream,
    required this.contentTitle,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(contentTitle),
        backgroundColor: Colors.indigo.shade100,
      ),
      backgroundColor: const Color(0xFFFAF8F5), // Warm cream color for comfortable long-form reading
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 32.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Clean typography header block separating content boundaries
              Text(
                contentTitle.toUpperCase(),
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: Colors.indigo.shade900,
                  letterSpacing: 1.5,
                ),
              ),
              const SizedBox(height: 16),
              const Divider(color: Colors.black45),
              const SizedBox(height: 24),

              // Standard layout content rendering area
              // For a production build, swap this Text widget with the
              // 'flutter_html' or 'flutter_markdown' package to process
              // inline styling tags (<p>, <b>, <i>) perfectly natively.
              Text(
                htmlDataStream,
                style: const TextStyle(
                  fontSize: 16.5,
                  height: 1.6,
                  fontFamily: 'Serif',
                  color: Color(0xFF2C2C2C),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}