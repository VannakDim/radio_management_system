import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../../core/network/api_client.dart';
import '../utils/image_editor_screen.dart';

class AddBorrowScreen extends StatefulWidget {
  const AddBorrowScreen({super.key});

  @override
  State<AddBorrowScreen> createState() => _AddBorrowScreenState();
}

class _AddBorrowScreenState extends State<AddBorrowScreen> {
  final ApiClient _apiClient = ApiClient();
  final _formKey = GlobalKey<FormState>();

  final _receiverController = TextEditingController();
  final _purposeController = TextEditingController();
  final _noteController = TextEditingController();

  XFile? _selectedImage;
  final ImagePicker _picker = ImagePicker();

  List<Map<String, dynamic>> _models = [];
  bool _isLoadingModels = true;
  String? _modelsError;

  // Selected radio items: { "model_id": int, "serial_number": string }
  final List<Map<String, dynamic>> _radioItems = [];

  // Selected accessory items: { "model_id": int, "quantity": int }
  final List<Map<String, dynamic>> _accessoryItems = [];

  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _fetchModels();
    _addRadioItemLine(); // Add one initial radio row
  }

  @override
  void dispose() {
    _receiverController.dispose();
    _purposeController.dispose();
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

  void _addRadioItemLine() {
    setState(() {
      _radioItems.add({
        'model_id': null,
        'serial_number': '',
        'controller': TextEditingController(),
      });
    });
  }

  void _removeRadioItemLine(int index) {
    setState(() {
      _radioItems[index]['controller']?.dispose();
      _radioItems.removeAt(index);
    });
  }

  void _addAccessoryItemLine() {
    setState(() {
      _accessoryItems.add({
        'model_id': null,
        'quantity': 1,
      });
    });
  }

  void _removeAccessoryItemLine(int index) {
    setState(() {
      _accessoryItems.removeAt(index);
    });
  }

  Future<void> _pickImage() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 80,
        maxWidth: 1200,
        maxHeight: 1200,
      );
      if (image != null && mounted) {
        final XFile? edited = await Navigator.push<XFile>(
          context,
          MaterialPageRoute(
            builder: (context) => ImageEditorScreen(imageFile: image),
          ),
        );
        if (edited != null) {
          setState(() {
            _selectedImage = edited;
          });
        }
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error picking image: $e')),
      );
    }
  }

  void _startScanning(int index) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) {
        return SizedBox(
          height: MediaQuery.of(context).size.height * 0.7,
          child: Column(
            children: [
              AppBar(
                title: const Text('ស្កេនបាកូដ / Scan Barcode'),
                automaticallyImplyLeading: false,
                actions: [
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(context),
                  )
                ],
              ),
              Expanded(
                child: MobileScanner(
                  onDetect: (capture) {
                    final List<Barcode> barcodes = capture.barcodes;
                    if (barcodes.isNotEmpty) {
                      final String? code = barcodes.first.rawValue;
                      if (code != null && code.isNotEmpty) {
                        setState(() {
                          _radioItems[index]['serial_number'] = code;
                          _radioItems[index]['controller']?.text = code;
                        });
                        Navigator.pop(context);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Scanned: $code')),
                        );
                      }
                    }
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;

    if (_radioItems.isEmpty && _accessoryItems.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('ត្រូវតែខ្ចីឧបករណ៍យ៉ាងតិច ១ / Must borrow at least 1 item')),
      );
      return;
    }

    // Validate radios
    for (var item in _radioItems) {
      if (item['model_id'] == null || item['serial_number'].toString().trim().isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('សូមជ្រើសរើសម៉ូដែល និងវាយលេខស៊េរីវិទ្យុ / Please select model and enter S/N')),
        );
        return;
      }
    }

    // Validate accessories
    for (var item in _accessoryItems) {
      if (item['model_id'] == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('សូមជ្រើសរើសម៉ូដែលគ្រឿងបន្លាស់ / Please select accessory model')),
        );
        return;
      }
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      Map<String, dynamic> payload = {
        'receiver': _receiverController.text,
        'purpose': _purposeController.text,
        'note': _noteController.text,
      };

      if (_radioItems.isNotEmpty) {
        for (int i = 0; i < _radioItems.length; i++) {
          payload['items[$i][model_id]'] = _radioItems[i]['model_id'];
          payload['items[$i][serial_number]'] = _radioItems[i]['serial_number'];
        }
      }

      if (_accessoryItems.isNotEmpty) {
        for (int i = 0; i < _accessoryItems.length; i++) {
          payload['accessories[$i][model_id]'] = _accessoryItems[i]['model_id'];
          payload['accessories[$i][quantity]'] = _accessoryItems[i]['quantity'];
        }
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
      final response = await _apiClient.dio.post('/borrows', data: formData);

      if (response.statusCode == 210 || (response.statusCode == 200 && response.data['success'])) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('ខ្ចីឧបករណ៍ជោគជ័យ / Borrow recorded successfully')),
        );
        Navigator.pop(context, true);
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
    
    final radioModels = _models.where((m) => m['accessory'] != true && m['accessory'] != 1 && m['accessory'] != '1').toList();
    final accessoryModels = _models.where((m) => m['accessory'] == true || m['accessory'] == 1 || m['accessory'] == '1').toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('បង្កើតការខ្ចីឧបករណ៍ / New Borrow'),
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
                        // General details
                        Card(
                          elevation: 1,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          child: Padding(
                            padding: const EdgeInsets.all(16.0),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'ព័ត៌មានស្នើសុំ / Request Info',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                ),
                                const SizedBox(height: 16),
                                TextFormField(
                                  controller: _receiverController,
                                  decoration: const InputDecoration(
                                    labelText: 'អ្នកខ្ចី (Receiver) *',
                                    border: OutlineInputBorder(),
                                    prefixIcon: Icon(Icons.person),
                                  ),
                                  validator: (value) {
                                    if (value == null || value.trim().isEmpty) {
                                      return 'Please enter receiver name';
                                    }
                                    return null;
                                  },
                                ),
                                const SizedBox(height: 16),
                                TextFormField(
                                  controller: _purposeController,
                                  decoration: const InputDecoration(
                                    labelText: 'គោលបំណង / Purpose *',
                                    border: OutlineInputBorder(),
                                    prefixIcon: Icon(Icons.info_outline),
                                  ),
                                  validator: (value) {
                                    if (value == null || value.trim().isEmpty) {
                                      return 'Please enter purpose';
                                    }
                                    return null;
                                  },
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Image attachment
                        const Text(
                          'ឯកសារខ្ចី/លិខិតស្នើសុំ / Request Document Photo',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 8),
                        GestureDetector(
                          onTap: _pickImage,
                          child: Container(
                            width: double.infinity,
                            height: 120,
                            decoration: BoxDecoration(
                              color: Colors.grey.shade100,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.grey.shade300),
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
                                      Icon(Icons.add_a_photo, size: 30, color: Colors.grey),
                                      SizedBox(height: 8),
                                      Text('ថតរូបឯកសារស្នើសុំ / Add Document Photo', style: TextStyle(color: Colors.grey)),
                                    ],
                                  ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Radios List Section
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'វិទ្យុទាក់ទងខ្ចី / Radios to Borrow',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.blue),
                            ),
                            TextButton.icon(
                              onPressed: _addRadioItemLine,
                              icon: const Icon(Icons.add),
                              label: const Text('Add Radio'),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: _radioItems.length,
                          itemBuilder: (context, index) {
                            final item = _radioItems[index];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 12),
                              color: Colors.blue.shade50.withOpacity(0.3),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                                side: BorderSide(color: Colors.blue.shade100),
                              ),
                              child: Padding(
                                padding: const EdgeInsets.all(12.0),
                                child: Column(
                                  children: [
                                    Row(
                                      children: [
                                        Expanded(
                                          child: DropdownButtonFormField<int>(
                                            value: item['model_id'],
                                            decoration: const InputDecoration(labelText: 'ម៉ូដែល / Model'),
                                            items: radioModels.map((m) {
                                              return DropdownMenuItem<int>(
                                                value: m['id'],
                                                child: Text(m['name'] ?? ''),
                                              );
                                            }).toList(),
                                            onChanged: (val) {
                                              setState(() {
                                                item['model_id'] = val;
                                              });
                                            },
                                          ),
                                        ),
                                        IconButton(
                                          icon: const Icon(Icons.delete, color: Colors.red),
                                          onPressed: () => _removeRadioItemLine(index),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    Row(
                                      children: [
                                        Expanded(
                                          child: TextFormField(
                                            controller: item['controller'],
                                            decoration: const InputDecoration(
                                              labelText: 'លេខសម្គាល់ឧបករណ៍ / S/N (PID)',
                                              prefixIcon: Icon(Icons.qr_code_scanner),
                                            ),
                                            onChanged: (val) {
                                              item['serial_number'] = val;
                                            },
                                          ),
                                        ),
                                        IconButton(
                                          icon: const Icon(Icons.camera_alt, color: Colors.blue),
                                          onPressed: () => _startScanning(index),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                        const SizedBox(height: 20),

                        // Accessories List Section
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'គ្រឿងបន្លាស់ខ្ចី / Accessories to Borrow',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.orange),
                            ),
                            TextButton.icon(
                              onPressed: _addAccessoryItemLine,
                              icon: const Icon(Icons.add),
                              label: const Text('Add Accessory'),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: _accessoryItems.length,
                          itemBuilder: (context, index) {
                            final item = _accessoryItems[index];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 12),
                              color: Colors.orange.shade50.withOpacity(0.3),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                                side: BorderSide(color: Colors.orange.shade100),
                              ),
                              child: Padding(
                                padding: const EdgeInsets.all(12.0),
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: DropdownButtonFormField<int>(
                                        value: item['model_id'],
                                        decoration: const InputDecoration(labelText: 'គ្រឿងបន្លាស់ / Accessory'),
                                        items: accessoryModels.map((m) {
                                          return DropdownMenuItem<int>(
                                            value: m['id'],
                                            child: Text(m['name'] ?? ''),
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
                                    SizedBox(
                                      width: 80,
                                      child: TextFormField(
                                        initialValue: '${item['quantity']}',
                                        keyboardType: TextInputType.number,
                                        decoration: const InputDecoration(labelText: 'ចំនួន / Qty'),
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
                                      onPressed: () => _removeAccessoryItemLine(index),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                        const SizedBox(height: 20),

                        // Note
                        TextFormField(
                          controller: _noteController,
                          maxLines: 2,
                          decoration: const InputDecoration(
                            labelText: 'កំណត់ចំណាំទូទៅ / General Note',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 32),

                        // Submit
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
                                    'រក្សាទុកការខ្ចីឧបករណ៍ / Submit Borrow Request',
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
