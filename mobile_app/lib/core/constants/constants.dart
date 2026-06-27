import 'package:flutter/foundation.dart';

class AppConstants {
  static String get baseUrl {
    if (kIsWeb) {
      // If using Laravel Sail on Docker (default port 80)
      return 'http://localhost/api';
      // If using php artisan serve (port 8000) on local host, uncomment below:
      // return 'http://localhost:8000/api';
    }
    // Default for Android Emulator
    return 'http://10.0.2.2:8000/api';
  }
  
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';

  static String? formatImageUrl(String? path) {
    if (path == null || path.isEmpty) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    
    // Clean base URL (remove trailing slash and /api)
    String base = baseUrl;
    if (base.endsWith('/api')) {
      base = base.substring(0, base.length - 4);
    }
    if (base.endsWith('/')) {
      base = base.substring(0, base.length - 1);
    }
    
    if (path.startsWith('uploads/')) {
      return '$base/storage/$path';
    }
    
    if (path.startsWith('storage/')) {
      return '$base/$path';
    }
    
    return '$base/storage/$path';
  }
}
