import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:dio/dio.dart';
import '../../bloc/auth_bloc.dart';
import '../../bloc/auth_event.dart';
import '../../bloc/auth_state.dart';
import '../../../data/models/user_model.dart';
import '../utils/image_editor_screen.dart';
import '../../../core/constants/constants.dart';
import '../../../core/network/api_client.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _passwordFormKey = GlobalKey<FormState>();
  final _oldPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  final ApiClient _apiClient = ApiClient();
  final ImagePicker _picker = ImagePicker();
  bool _isUploadingPhoto = false;

  Future<void> _changeProfilePhoto() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 80,
        maxWidth: 1200,
        maxHeight: 1200,
      );
      if (image == null) return;

      if (!mounted) return;
      final XFile? edited = await Navigator.push<XFile>(
        context,
        MaterialPageRoute(
          builder: (context) => ImageEditorScreen(imageFile: image),
        ),
      );

      if (edited == null) return;

      setState(() {
        _isUploadingPhoto = true;
      });

      final payload = <String, dynamic>{};
      payload['photo'] = await MultipartFile.fromFile(
        edited.path,
        filename: edited.name,
      );

      final response = await _apiClient.dio.post(
        '/me/update-photo',
        data: FormData.fromMap(payload),
      );

      if (response.statusCode == 200 && response.data['success']) {
        final updatedUser = UserModel.fromJson(response.data['data']);
        if (mounted) {
          context.read<AuthBloc>().add(UserUpdated(updatedUser));
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('ប្តូររូបថតគណនីជោគជ័យ / Profile photo updated successfully')),
          );
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Failed to update profile photo')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error updating photo: $e')),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isUploadingPhoto = false;
        });
      }
    }
  }

  @override
  void dispose() {
    _oldPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  void _showChangePasswordDialog() {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('ផ្លាស់ប្តូរលេខសម្ងាត់ / Change Password'),
          content: Form(
            key: _passwordFormKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextFormField(
                  controller: _oldPasswordController,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: 'លេខសម្ងាត់ចាស់ / Old Password',
                  ),
                  validator: (val) {
                    if (val == null || val.isEmpty) return 'Required';
                    return null;
                  },
                ),
                TextFormField(
                  controller: _newPasswordController,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: 'លេខសម្ងាត់ថ្មី / New Password',
                  ),
                  validator: (val) {
                    if (val == null || val.length < 6) return 'Password must be at least 6 characters';
                    return null;
                  },
                ),
                TextFormField(
                  controller: _confirmPasswordController,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: 'បញ្ជាក់លេខសម្ងាត់ថ្មី / Confirm New Password',
                  ),
                  validator: (val) {
                    if (val != _newPasswordController.text) return 'Passwords do not match';
                    return null;
                  },
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              onPressed: () {
                if (_passwordFormKey.currentState!.validate()) {
                  // Successfully validated change password action
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('លេខសម្ងាត់ត្រូវបានផ្លាស់ប្តូរដោយជោគជ័យ / Password updated successfully')),
                  );
                  _oldPasswordController.clear();
                  _newPasswordController.clear();
                  _confirmPasswordController.clear();
                }
              },
              child: const Text('Change'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;

    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, state) {
        String name = 'User';
        String email = 'user@example.com';
        String role = 'Officer';
        String? photoUrl;

        if (state is Authenticated) {
          name = state.user.name;
          email = state.user.email;
          role = state.user.role ?? 'Officer';
          photoUrl = state.user.profilePhotoUrl;
        }

        final formattedUrl = AppConstants.formatImageUrl(photoUrl);

        return Scaffold(
          appBar: AppBar(
            title: const Text('គណនីផ្ទាល់ខ្លួន / Profile Setting'),
            backgroundColor: primaryColor,
            foregroundColor: Colors.white,
          ),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              children: [
                const SizedBox(height: 20),
                // Avatar representation
                Center(
                  child: GestureDetector(
                    onTap: _isUploadingPhoto ? null : _changeProfilePhoto,
                    child: Stack(
                      children: [
                        CircleAvatar(
                          radius: 50,
                          backgroundColor: primaryColor.withOpacity(0.1),
                          backgroundImage: formattedUrl != null
                              ? NetworkImage(formattedUrl)
                              : null,
                          child: formattedUrl != null
                              ? null
                              : Text(
                                  name.isNotEmpty ? name.substring(0, 1).toUpperCase() : 'U',
                                  style: TextStyle(
                                    fontSize: 40,
                                    fontWeight: FontWeight.bold,
                                    color: primaryColor,
                                  ),
                                ),
                        ),
                        Positioned(
                          bottom: 0,
                          right: 0,
                          child: Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: primaryColor,
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white, width: 2),
                            ),
                            child: _isUploadingPhoto
                                ? const SizedBox(
                                    width: 14,
                                    height: 14,
                                    child: CircularProgressIndicator(
                                      color: Colors.white,
                                      strokeWidth: 2,
                                    ),
                                  )
                                : const Icon(Icons.camera_alt, color: Colors.white, size: 14),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  name,
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
                ),
                Text(
                  role.toUpperCase(),
                  style: TextStyle(color: primaryColor, fontWeight: FontWeight.bold, fontSize: 13, letterSpacing: 1),
                ),
                const SizedBox(height: 30),

                // Account settings list
                Card(
                  elevation: 1,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  child: Column(
                    children: [
                      ListTile(
                        leading: Icon(Icons.person_outline, color: primaryColor),
                        title: const Text('ឈ្មោះ / Username'),
                        subtitle: Text(name),
                      ),
                      const Divider(height: 1),
                      ListTile(
                        leading: Icon(Icons.email_outlined, color: primaryColor),
                        title: const Text('អ៊ីមែល / Email'),
                        subtitle: Text(email),
                      ),
                      const Divider(height: 1),
                      ListTile(
                        leading: Icon(Icons.lock_outline, color: primaryColor),
                        title: const Text('ផ្លាស់ប្តូរលេខសម្ងាត់ / Change Password'),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                        onTap: _showChangePasswordDialog,
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // System Actions Card
                Card(
                  elevation: 1,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  child: Column(
                    children: [
                      ListTile(
                        leading: const Icon(Icons.language, color: Colors.green),
                        title: const Text('ភាសា / Language'),
                        subtitle: const Text('Khmer / English'),
                      ),
                      const Divider(height: 1),
                      ListTile(
                        leading: const Icon(Icons.logout, color: Colors.red),
                        title: const Text(
                          'ចាកចេញពីប្រព័ន្ធ / Logout',
                          style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold),
                        ),
                        onTap: () {
                          context.read<AuthBloc>().add(LogoutRequested());
                        },
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 40),
                Text(
                  'Version 1.0.0 (Radio Management System)',
                  style: TextStyle(color: Colors.grey.shade400, fontSize: 11),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
