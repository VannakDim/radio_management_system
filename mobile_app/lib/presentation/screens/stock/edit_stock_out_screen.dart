import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../../core/constants/constants.dart';
import '../../../core/network/api_client.dart';
import '../utils/image_editor_screen.dart';

class EditStockOutScreen extends StatefulWidget {
  final Map<String, dynamic> item;

  const EditStockOutScreen({super.key, required this.item});

  @override
  State<EditStockOutScreen> createState() => _EditStockOutScreenState();
}

class _EditStockOutScreenState extends State<EditStockOutScreen> {
  final ApiClient _apiClient = ApiClient();
  final _formKey = GlobalKey<FormState>();

  late final TextEditingController _receiverController;
  late final TextEditingController _purposeController;
  late final TextEditingController _noteController;

  XFile? _selectedImage;
  final ImagePicker _picker = ImagePicker();
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _receiverController = TextEditingController(text: widget.item['receiver'] ?? '');
    _purposeController = TextEditingController(text: widget.item['type'] ?? '');
    _noteController = TextEditingController(text: widget.item['note'] ?? '');
  }

  @override
  void dispose() {
    _receiverController.dispose();
    _purposeController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final XFile? image = await _picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
    if (image == null) return;

    if (!kIsWeb) {
      final editedImage = await Navigator.push<XFile?>(
        context,
        MaterialPageRoute(builder: (context) => ImageEditorScreen(imageFile: image)),
      );
      if (editedImage != null) setState(() => _selectedImage = editedImage);
    } else {
      setState(() => _selectedImage = image);
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    try {
      final id = widget.item['id'];
      final Map<String, dynamic> payload = {
        'receiver': _receiverController.text,
        'type': _purposeController.text,
        'note': _noteController.text,
      };

      if (_selectedImage != null) {
        if (kIsWeb) {
          payload['image'] = MultipartFile.fromBytes(
            await _selectedImage!.readAsBytes(),
            filename: _selectedImage!.name,
          );
        } else {
          payload['image'] = await MultipartFile.fromFile(
            _selectedImage!.path,
            filename: _selectedImage!.name,
          );
        }
      }

      final formData = FormData.fromMap(payload);
      final response = await _apiClient.dio.post('/stock-outs/$id/update', data: formData);

      if (!mounted) return;

      if (response.data['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Stock Out updated successfully ✓'), backgroundColor: Colors.green),
        );
        Navigator.pop(context, true);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(response.data['message'] ?? 'Update failed'), backgroundColor: Colors.red),
        );
      }
    } on DioException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.response?.data['message'] ?? 'Connection error'), backgroundColor: Colors.red),
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;
    final existingImageUrl = AppConstants.formatImageUrl(widget.item['image']);

    return Scaffold(
      appBar: AppBar(
        title: const Text('កែប្រែ Stock Out / Edit Stock Out'),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        actions: [
          if (_isSubmitting)
            const Padding(
              padding: EdgeInsets.all(16),
              child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)),
            )
          else
            IconButton(
              icon: const Icon(Icons.save),
              tooltip: 'Save',
              onPressed: _submit,
            ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Info Card
              Card(
                elevation: 1,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'ព័ត៌មានទូទៅ / General Info',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _receiverController,
                        decoration: const InputDecoration(
                          labelText: 'Receiver / អ្នកទទួល *',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.person),
                        ),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'សូមបញ្ចូលឈ្មោះអ្នកទទួល' : null,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _purposeController,
                        decoration: const InputDecoration(
                          labelText: 'គោលបំណង / Purpose *',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.info_outline),
                        ),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'សូមបញ្ចូលគោលបំណង' : null,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _noteController,
                        maxLines: 3,
                        decoration: const InputDecoration(
                          labelText: 'Note / កំណត់សម្គាល់',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.notes),
                          alignLabelWithHint: true,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Image Picker
              const Text('រូបភាព / Image (Optional)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              const SizedBox(height: 8),
              GestureDetector(
                onTap: _pickImage,
                child: Container(
                  width: double.infinity,
                  height: 180,
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.blue.shade200, width: 2, style: BorderStyle.solid),
                    borderRadius: BorderRadius.circular(12),
                    color: Colors.blue.shade50,
                  ),
                  child: _selectedImage != null
                      ? ClipRRect(
                          borderRadius: BorderRadius.circular(10),
                          child: kIsWeb
                              ? Image.network(_selectedImage!.path, fit: BoxFit.cover)
                              : Image.file(File(_selectedImage!.path), fit: BoxFit.cover),
                        )
                      : existingImageUrl != null
                          ? Stack(
                              children: [
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(10),
                                  child: Image.network(
                                    existingImageUrl,
                                    width: double.infinity,
                                    height: 180,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) => const Center(child: Icon(Icons.broken_image, size: 40, color: Colors.grey)),
                                  ),
                                ),
                                Positioned(
                                  bottom: 8, right: 8,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                    decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(8)),
                                    child: const Text('ចុចដើម្បីប្ដូរ / Tap to change', style: TextStyle(color: Colors.white, fontSize: 12)),
                                  ),
                                ),
                              ],
                            )
                          : Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.add_photo_alternate, size: 48, color: Colors.blue.shade400),
                                const SizedBox(height: 8),
                                Text('ជ្រើសរើសរូបភាព / Select Image', style: TextStyle(color: Colors.blue.shade600)),
                              ],
                            ),
                ),
              ),
              const SizedBox(height: 30),

              // Submit button
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton.icon(
                  onPressed: _isSubmitting ? null : _submit,
                  icon: _isSubmitting
                      ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Icon(Icons.save),
                  label: Text(_isSubmitting ? 'កំពុងរក្សាទុក...' : 'រក្សាទុក / Save Changes'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: primaryColor,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
