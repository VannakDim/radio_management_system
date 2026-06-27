import 'package:flutter_test/flutter_test.dart';
import 'package:mobile_app/data/models/user_model.dart';

void main() {
  group('UserModel Tests', () {
    test('should parse user model from json correctly', () {
      // Arrange
      final json = {
        'id': 1,
        'name': 'Test User',
        'email': 'test@example.com',
        'role': 'admin',
        'profile_photo_url': 'http://example.com/photo.jpg',
      };

      // Act
      final user = UserModel.fromJson(json);

      // Assert
      expect(user.id, 1);
      expect(user.name, 'Test User');
      expect(user.email, 'test@example.com');
      expect(user.role, 'admin');
      expect(user.profilePhotoUrl, 'http://example.com/photo.jpg');
    });

    test('should convert user model to json correctly', () {
      // Arrange
      final user = UserModel(
        id: 2,
        name: 'Jane Doe',
        email: 'jane@example.com',
        role: 'user',
        profilePhotoUrl: null,
      );

      // Act
      final json = user.toJson();

      // Assert
      expect(json['id'], 2);
      expect(json['name'], 'Jane Doe');
      expect(json['email'], 'jane@example.com');
      expect(json['role'], 'user');
      expect(json['profile_photo_url'], null);
    });
  });
}
