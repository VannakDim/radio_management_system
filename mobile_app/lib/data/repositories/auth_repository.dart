import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../../core/constants/constants.dart';
import '../../core/network/api_client.dart';
import '../models/user_model.dart';

class AuthRepository {
  final ApiClient _apiClient;
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  AuthRepository(this._apiClient);

  Future<UserModel?> login(String email, String password) async {
    try {
      final response = await _apiClient.dio.post('/login', data: {
        'email': email,
        'password': password,
      });

      if (response.statusCode == 200 && response.data['success']) {
        final token = response.data['data']['token'];
        final userData = response.data['data']['user'];
        
        await _storage.write(key: AppConstants.tokenKey, value: token);
        
        final user = UserModel.fromJson(userData);
        await _storage.write(key: AppConstants.userKey, value: jsonEncode(user.toJson()));
        
        return user;
      }
      return null;
    } on DioException catch (e) {
      throw Exception(e.response?.data['message'] ?? 'Login failed');
    }
  }

  Future<void> logout() async {
    try {
      await _apiClient.dio.post('/logout');
    } catch (_) {
      // Continue cleanup even if server logout fails
    } finally {
      await _storage.delete(key: AppConstants.tokenKey);
      await _storage.delete(key: AppConstants.userKey);
    }
  }

  Future<UserModel?> getCurrentUser() async {
    try {
      final cachedUser = await _storage.read(key: AppConstants.userKey);
      if (cachedUser != null) {
        return UserModel.fromJson(jsonDecode(cachedUser));
      }

      final response = await _apiClient.dio.get('/me');
      if (response.statusCode == 200 && response.data['success']) {
        final user = UserModel.fromJson(response.data['data']);
        await _storage.write(key: AppConstants.userKey, value: jsonEncode(user.toJson()));
        return user;
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  Future<bool> isLoggedIn() async {
    final token = await _storage.read(key: AppConstants.tokenKey);
    return token != null;
  }

  Future<void> updateCachedUser(UserModel user) async {
    await _storage.write(key: AppConstants.userKey, value: jsonEncode(user.toJson()));
  }
}
