import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../../core/network/api_client.dart';

class AddStockInScreen extends StatefulWidget {
  const AddStockInScreen({super.key});

  @override
  State<AddStockInScreen> createState() => _AddStockInScreenState();
}

class _AddStockInScreenState extends State<AddStockInScreen> {
  final ApiClient _apiClient = ApiClient();
  final _formKey = GlobalKey<FormState>();
  
  final _invoiceController = TextEditingController();
  final _supplierController = TextEditingController();
  final _noteController = TextEditingController();
  
  XFile? _selectedImage;
  final ImagePicker _picker = ImagePicker();
  
  List<Map<String, dynamic>> _models = [];
  bool _isLoadingModels = true;
  String? _modelsError;

  // Selected items list: each item is { "model_id": int, "quantity": int, "note": string }
  final List<Map<String, dynamic>> _items = [];

  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _fetchModels();
    _addItemLine(); // Start with one empty item line
  }

  @override
  void dispose() {
    _invoiceController.dispose();
    _supplierController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  Future<void> _fetchModels() async {
    try {
      final response = await _apiClient.dio.get('/product-models');
      if (response.statusCode == 200 && response.data['success']) {
        setState(() {
          _models = List<Map<String, dynamic>>.from(response.data['data']);
          _isLoadingModels = false;
        });
      } else {
        setState(() {
          _modelsError = 'Failed to load product models';
          _isLoadingModels = false;
        });
      }
    } catch (e) {
      setState(() {
        _modelsError = 'Error connecting to server';
        _isLoadingModels = false;
      });
    }
  }

  void _addItemLine() {
    setState(() {
      _items.add({
        'model_id': null,
        'quantity': 1,
        'note': '',
      });
    });
  }

  void _removeItemLine(int index) {
    if (_items.length > 1) {
      setState(() {
        _items.removeAt(index);
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('ត្រូវតែមានទំនិញយ៉ាងតិច ១ / Must have at least 1 item')),
      );
    }
  }

  Future<void> _pickImage() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 80,
      );
      if (image != null) {
        setState(() {
          _selectedImage = image;
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error picking image: $e')),
      );
    }
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;

    // Validate that all items have a model selected
    for (var item in _items) {
      if (item['model_id'] == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('សូមជ្រើសរើសម៉ូដែលឧបករណ៍សម្រាប់គ្រប់ជួរ / Please select a model for all rows')),
        );
        return;
      }
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      Map<String, dynamic> payload = {
        'invoice_no': _invoiceController.text,
        'supplier': _supplierController.text,
        'note': _noteController.text,
      };

      // Format items array for multipart form data
      for (int i = 0; i < _items.length; i++) {
        payload['items[$i][model_id]'] = _items[i]['model_id'];
        payload['items[$i][quantity]'] = _items[i]['quantity'];
        payload['items[$i][note]'] = _items[i]['note'];
      }

      if (_selectedImage != null) {
        if (kIsWeb) {
          final bytes = await _selectedImage!.readAsBytes();
          payload['image'] = MultipartFile.fromBytes(
            bytes,
            filename: _selectedImage!.name,
          );
        } else {
          payload['image'] = await MultipartFile.fromFile(
            _selectedImage!.path,
            filename: _selectedImage!.name,
          );
        }
      }

      FormData formData = FormData.fromMap(payload);
      final response = await _apiClient.dio.post('/stock-ins', data: formData);

      if (response.statusCode == 210 || (response.statusCode == 200 && response.data['success'])) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('នាំចូលស្តុកជោគជ័យ / Stock In recorded successfully')),
        );
        Navigator.pop(context, true); // Return true to trigger refresh
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed: ${response.data['message']}')),
        );
      }
    } on DioException catch (e) {
      final msg = e.response?.data['message'] ?? 'Connection error';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $msg')),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Unexpected error: $e')),
      );
    } finally {
      setState(() {
        _isSubmitting = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;

    return Scaffold(
      appBar: AppBar(
        title: const Text('នាំចូលស្តុកថ្មី / New Stock In'),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
      ),
      body: _isLoadingModels
          ? const Center(child: CircularProgressIndicator())
          : _modelsError != null
              ? Center(child: Text(_modelsError!))
              : Form(
                  key: _formKey,
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Supplier & Invoice Info Card
                        Card(
                          elevation: 1,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          child: Padding(
                            padding: const EdgeInsets.all(16.0),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'ព័ត៌មានវិក្កយបត្រ / Invoice Info',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                ),
                                const SizedBox(height: 16),
                                TextFormField(
                                  controller: _invoiceController,
                                  decoration: const InputDecoration(
                                    labelText: 'លេខវិក្កយបត្រ / Invoice No',
                                    border: OutlineInputBorder(),
                                    prefixIcon: Icon(Icons.receipt_long),
                                  ),
                                ),
                                const SizedBox(height: 16),
                                TextFormField(
                                  controller: _supplierController,
                                  decoration: const InputDecoration(
                                    labelText: 'អ្នកផ្គត់ផ្គង់ / Supplier',
                                    border: OutlineInputBorder(),
                                    prefixIcon: Icon(Icons.business),
                                  ),
                                  validator: (value) {
                                    if (value == null || value.trim().isEmpty) {
                                      return 'សូមបញ្ចូលឈ្មោះអ្នកផ្គត់ផ្គង់ / Please enter supplier';
                                    }
                                    return null;
                                  },
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Image Picker Section
                        const Text(
                          'ឯកសារភ្ជាប់ / Attach Document Image',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 8),
                        GestureDetector(
                          onTap: _pickImage,
                          child: Container(
                            width: double.infinity,
                            height: 150,
                            decoration: BoxDecoration(
                              color: Colors.grey.shade100,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.grey.shade300, style: BorderStyle.solid),
                            ),
                            child: _selectedImage != null
                                ? ClipRRect(
                                    borderRadius: BorderRadius.circular(12),
                                    child: kIsWeb
                                        ? Image.network(_selectedImage!.path, fit: BoxFit.cover)
                                        : Image.file(File(_selectedImage!.path), fit: BoxFit.cover),
                                  )
                                : const Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(Icons.add_a_photo, size: 40, color: Colors.grey),
                                      SizedBox(height: 8),
                                      Text(
                                        'ជ្រើសរើសរូបភាពវិក្កយបត្រ / Select Invoice Photo',
                                        style: TextStyle(color: Colors.grey),
                                      ),
                                    ],
                                  ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Items Section Title
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'បញ្ជីឧបករណ៍នាំចូល / Stock In Items',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                            TextButton.icon(
                              onPressed: _addItemLine,
                              icon: const Icon(Icons.add),
                              label: const Text('Add Row'),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),

                        // Item Rows
                        ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: _items.length,
                          itemBuilder: (context, index) {
                            final item = _items[index];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 12),
                              color: Colors.grey.shade50,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                                side: BorderSide(color: Colors.grey.shade200),
                              ),
                              child: Padding(
                                padding: const EdgeInsets.all(12.0),
                                child: Column(
                                  children: [
                                    Row(
                                      children: [
                                        // Model Dropdown
                                        Expanded(
                                          child: DropdownButtonFormField<int>(
                                            value: item['model_id'],
                                            decoration: const InputDecoration(
                                              labelText: 'ម៉ូដែល / Model',
                                              contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                            ),
                                            items: _models.map((model) {
                                              return DropdownMenuItem<int>(
                                                value: model['id'],
                                                child: Text(model['name'] ?? 'Unknown'),
                                              );
                                            }).toList(),
                                            onChanged: (val) {
                                              setState(() {
                                                item['model_id'] = val;
                                              });
                                            },
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        // Quantity
                                        SizedBox(
                                          width: 80,
                                          child: TextFormField(
                                            initialValue: '${item['quantity']}',
                                            keyboardType: TextInputType.number,
                                            decoration: const InputDecoration(
                                              labelText: 'ចំនួន / Qty',
                                              contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                            ),
                                            validator: (val) {
                                              if (val == null || int.tryParse(val) == null || int.parse(val) <= 0) {
                                                return 'Invalid';
                                              }
                                              return null;
                                            },
                                            onChanged: (val) {
                                              item['quantity'] = int.tryParse(val) ?? 1;
                                            },
                                          ),
                                        ),
                                        IconButton(
                                          icon: const Icon(Icons.delete, color: Colors.red),
                                          onPressed: () => _removeItemLine(index),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    // Row Note
                                    TextFormField(
                                      initialValue: item['note'],
                                      decoration: const InputDecoration(
                                        labelText: 'កំណត់ចំណាំឧបករណ៍ / Item Note',
                                        contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      ),
                                      onChanged: (val) {
                                        item['note'] = val;
                                      },
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                        const SizedBox(height: 20),

                        // General Note
                        TextFormField(
                          controller: _noteController,
                          maxLines: 2,
                          decoration: const InputDecoration(
                            labelText: 'កំណត់ចំណាំទូទៅ / General Note',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 32),

                        // Submit Button
                        SizedBox(
                          width: double.infinity,
                          height: 50,
                          child: ElevatedButton(
                            onPressed: _isSubmitting ? null : _submitForm,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: primaryColor,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            child: _isSubmitting
                                ? const CircularProgressIndicator(color: Colors.white)
                                : const Text(
                                    'រក្សាទុកការនាំចូល / Submit Stock In',
                                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
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
