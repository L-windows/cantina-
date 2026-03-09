import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Change this to your local IP when testing on physical device
  static const String baseUrl = 'http://127.0.0.1:8000/api/v1';

  static Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  static Future<Map<String, String>> _authHeaders() async {
    final token = await _getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // AUTH
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> register(String name, String email, String password, String role) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/register'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'name': name, 'email': email, 'password': password, 'password_confirmation': password, 'role': role}),
    );
    return jsonDecode(response.body);
  }

  static Future<void> logout() async {
    final headers = await _authHeaders();
    await http.post(Uri.parse('$baseUrl/auth/logout'), headers: headers);
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_data');
  }

  // STUDENTS
  static Future<List<dynamic>> getStudents() async {
    final headers = await _authHeaders();
    final response = await http.get(Uri.parse('$baseUrl/students'), headers: headers);
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> createStudent(String name, String? birthDate) async {
    final headers = await _authHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/students'),
      headers: headers,
      body: jsonEncode({'name': name, 'birth_date': birthDate}),
    );
    return jsonDecode(response.body);
  }

  // WALLET
  static Future<Map<String, dynamic>> topup(int walletId, double amount) async {
    final headers = await _authHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/wallet/topup'),
      headers: headers,
      body: jsonEncode({'wallet_id': walletId, 'amount': amount}),
    );
    return jsonDecode(response.body);
  }

  static Future<List<dynamic>> getTransactionHistory({int? walletId}) async {
    final headers = await _authHeaders();
    final url = walletId != null
        ? '$baseUrl/wallet/history?wallet_id=$walletId'
        : '$baseUrl/wallet/history';
    final response = await http.get(Uri.parse(url), headers: headers);
    return jsonDecode(response.body);
  }
}
