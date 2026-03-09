import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import 'login_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;
  List<dynamic> _students = [];
  List<dynamic> _transactions = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final students = await ApiService.getStudents();
      List<dynamic> txns = [];
      if (students.isNotEmpty) {
        final walletId = students[0]['wallet']?['id'];
        if (walletId != null) {
          txns = await ApiService.getTransactionHistory(walletId: walletId);
        }
      }
      setState(() {
        _students = students;
        _transactions = txns;
        _loading = false;
      });
    } catch (e) {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return Scaffold(
      backgroundColor: const Color(0xFF080818),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFFD4AF37)))
          : IndexedStack(
              index: _selectedIndex,
              children: [
                _buildWalletTab(user),
                _buildStudentsTab(),
                _buildHistoryTab(),
              ],
            ),
      bottomNavigationBar: BottomNavigationBar(
        backgroundColor: const Color(0xFF0D0D22),
        selectedItemColor: const Color(0xFFD4AF37),
        unselectedItemColor: Colors.white38,
        currentIndex: _selectedIndex,
        onTap: (i) => setState(() => _selectedIndex = i),
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.account_balance_wallet), label: 'Carteira'),
          BottomNavigationBarItem(icon: Icon(Icons.people), label: 'Alunos'),
          BottomNavigationBarItem(icon: Icon(Icons.history), label: 'Histórico'),
        ],
      ),
    );
  }

  Widget _buildWalletTab(Map<String, dynamic>? user) {
    final firstStudent = _students.isNotEmpty ? _students[0] : null;
    final balance = firstStudent?['wallet']?['balance'] ?? 0.0;
    final qrCode = firstStudent?['qr_code'] ?? '';

    return SafeArea(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Olá, ${user?['name']?.split(' ').first ?? 'Utilizador'} 👋',
                        style: GoogleFonts.outfit(color: Colors.white54, fontSize: 14)),
                    Text('Mine Pay', style: GoogleFonts.outfit(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                  ],
                ),
                IconButton(
                  icon: const Icon(Icons.logout, color: Colors.white54),
                  onPressed: () async {
                    await context.read<AuthProvider>().logout();
                    if (mounted) {
                      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const LoginScreen()));
                    }
                  },
                ),
              ],
            ),
            const SizedBox(height: 24),
            // Balance Card
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFD4AF37), Color(0xFFB8860B)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(20),
                boxShadow: [BoxShadow(color: Colors.amber.withOpacity(0.3), blurRadius: 20, offset: const Offset(0, 8))],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Saldo Disponível', style: GoogleFonts.outfit(color: Colors.black54, fontSize: 14)),
                  const SizedBox(height: 8),
                  Text('${double.parse(balance.toString()).toStringAsFixed(2)} Kz',
                      style: GoogleFonts.outfit(color: Colors.black, fontSize: 36, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text(firstStudent?['name'] ?? 'Sem aluno cadastrado',
                      style: GoogleFonts.outfit(color: Colors.black54, fontSize: 12)),
                ],
              ),
            ),
            const SizedBox(height: 20),
            // QR Code
            if (qrCode.isNotEmpty) ...[
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: const Color(0xFF0D0D22),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFF1E1E3E)),
                ),
                child: Column(
                  children: [
                    Text('QR Code de Pagamento', style: GoogleFonts.outfit(color: Colors.white70, fontSize: 14)),
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)),
                      child: QrImageView(data: qrCode, size: 160),
                    ),
                    const SizedBox(height: 12),
                    Text(qrCode, style: GoogleFonts.robotoMono(color: Colors.white38, fontSize: 12)),
                  ],
                ),
              ),
              const SizedBox(height: 16),
            ],
            // Topup button
            if (firstStudent != null)
              ElevatedButton.icon(
                onPressed: () => _showTopupDialog(firstStudent['wallet']['id']),
                icon: const Icon(Icons.add),
                label: Text('Recarregar Saldo', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF1E1E3E),
                  foregroundColor: const Color(0xFFD4AF37),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildStudentsTab() {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Meus Alunos', style: GoogleFonts.outfit(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                IconButton(
                  icon: const Icon(Icons.add_circle, color: Color(0xFFD4AF37), size: 30),
                  onPressed: _showAddStudentDialog,
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (_students.isEmpty)
              Center(child: Text('Nenhum aluno cadastrado', style: GoogleFonts.outfit(color: Colors.white54)))
            else
              ..._students.map((s) => Card(
                color: const Color(0xFF0D0D22),
                margin: const EdgeInsets.only(bottom: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: const Color(0xFFD4AF37),
                    child: Text(s['name'][0], style: const TextStyle(color: Colors.black, fontWeight: FontWeight.bold)),
                  ),
                  title: Text(s['name'], style: GoogleFonts.outfit(color: Colors.white)),
                  subtitle: Text('Saldo: ${double.parse((s['wallet']?['balance'] ?? 0.0).toString()).toStringAsFixed(2)} Kz',
                      style: GoogleFonts.outfit(color: Colors.white54, fontSize: 12)),
                  trailing: Text(s['qr_code'] ?? '', style: GoogleFonts.robotoMono(color: Colors.white38, fontSize: 10)),
                ),
              )),
          ],
        ),
      ),
    );
  }

  Widget _buildHistoryTab() {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Histórico de Transações', style: GoogleFonts.outfit(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            if (_transactions.isEmpty)
              Center(child: Text('Nenhuma transação ainda', style: GoogleFonts.outfit(color: Colors.white54)))
            else
              Expanded(
                child: ListView.builder(
                  itemCount: _transactions.length,
                  itemBuilder: (ctx, i) {
                    final t = _transactions[i];
                    final amt = double.parse(t['amount'].toString());
                    final isPositive = amt > 0;
                    return Card(
                      color: const Color(0xFF0D0D22),
                      margin: const EdgeInsets.only(bottom: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: isPositive ? Colors.green.shade900 : Colors.red.shade900,
                          child: Icon(isPositive ? Icons.arrow_downward : Icons.arrow_upward,
                              color: isPositive ? Colors.green : Colors.red, size: 20),
                        ),
                        title: Text(t['description'] ?? t['type'], style: GoogleFonts.outfit(color: Colors.white, fontSize: 14)),
                        subtitle: Text(t['created_at']?.toString().substring(0, 10) ?? '',
                            style: GoogleFonts.outfit(color: Colors.white38, fontSize: 12)),
                        trailing: Text(
                          '${isPositive ? '+' : ''}${amt.toStringAsFixed(2)} Kz',
                          style: GoogleFonts.outfit(color: isPositive ? Colors.green : Colors.red, fontWeight: FontWeight.bold),
                        ),
                      ),
                    );
                  },
                ),
              ),
          ],
        ),
      ),
    );
  }

  void _showTopupDialog(int walletId) {
    final ctrl = TextEditingController();
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: const Color(0xFF0D0D22),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('Recarregar Saldo', style: GoogleFonts.outfit(color: Colors.white)),
        content: TextField(
          controller: ctrl,
          keyboardType: TextInputType.number,
          style: GoogleFonts.outfit(color: Colors.white),
          decoration: InputDecoration(
            labelText: 'Valor (Kz)',
            labelStyle: GoogleFonts.outfit(color: Colors.white54),
            enabledBorder: const OutlineInputBorder(borderSide: BorderSide(color: Color(0xFF2A2A4A))),
            focusedBorder: const OutlineInputBorder(borderSide: BorderSide(color: Color(0xFFD4AF37))),
            filled: true,
            fillColor: const Color(0xFF12122A),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: Text('Cancelar', style: GoogleFonts.outfit(color: Colors.white54))),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFD4AF37), foregroundColor: Colors.black),
            onPressed: () async {
              final amount = double.tryParse(ctrl.text);
              if (amount != null && amount > 0) {
                Navigator.pop(context);
                await ApiService.topup(walletId, amount);
                _loadData();
              }
            },
            child: Text('Recarregar', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _showAddStudentDialog() {
    final ctrl = TextEditingController();
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: const Color(0xFF0D0D22),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('Adicionar Aluno', style: GoogleFonts.outfit(color: Colors.white)),
        content: TextField(
          controller: ctrl,
          style: GoogleFonts.outfit(color: Colors.white),
          decoration: InputDecoration(
            labelText: 'Nome do Aluno',
            labelStyle: GoogleFonts.outfit(color: Colors.white54),
            enabledBorder: const OutlineInputBorder(borderSide: BorderSide(color: Color(0xFF2A2A4A))),
            focusedBorder: const OutlineInputBorder(borderSide: BorderSide(color: Color(0xFFD4AF37))),
            filled: true,
            fillColor: const Color(0xFF12122A),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: Text('Cancelar', style: GoogleFonts.outfit(color: Colors.white54))),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFD4AF37), foregroundColor: Colors.black),
            onPressed: () async {
              if (ctrl.text.isNotEmpty) {
                Navigator.pop(context);
                await ApiService.createStudent(ctrl.text, null);
                _loadData();
              }
            },
            child: Text('Adicionar', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }
}
