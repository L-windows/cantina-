import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:mine_pay_mobile/main.dart';
import 'package:mine_pay_mobile/providers/auth_provider.dart';

void main() {
  testWidgets('App launches smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(
      ChangeNotifierProvider(
        create: (_) => AuthProvider(),
        child: const MinePayApp(),
      ),
    );
    expect(find.byType(MaterialApp), findsOneWidget);
  });
}
