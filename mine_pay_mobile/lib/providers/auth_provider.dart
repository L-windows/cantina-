import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  Map<String, dynamic>? _user;
  bool _isLoading = false;
  String? _error;

  Map<String, dynamic>? get user => _user;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _user != null;
  String? get error => _error;

  AuthProvider() {
    _loadUser();
  }

  Future<void> _loadUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userData = prefs.getString('user_data');
    if (userData != null) {
      _user = jsonDecode(userData);
      notifyListeners();
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final result = await ApiService.login(email, password);
      if (result['token'] != null) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', result['token']);
        await prefs.setString('user_data', jsonEncode(result['user']));
        _user = result['user'];
        _isLoading = false;
        notifyListeners();
        return true;
      }
      _error = result['message'] ?? 'Login failed';
    } catch (e) {
      _error = e.toString();
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<bool> register(String name, String email, String password, String role) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final result = await ApiService.register(name, email, password, role);
      if (result['token'] != null) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', result['token']);
        await prefs.setString('user_data', jsonEncode(result['user']));
        _user = result['user'];
        _isLoading = false;
        notifyListeners();
        return true;
      }
      _error = result['message'] ?? 'Registration failed';
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<void> logout() async {
    await ApiService.logout();
    _user = null;
    notifyListeners();
  }
}
