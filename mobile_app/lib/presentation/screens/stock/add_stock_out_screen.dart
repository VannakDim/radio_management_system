import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../../core/network/api_client.dart';

class AddStockOutScreen extends StatefulWidget {
  const AddStockOutScreen({super.key});

  @override
  State<AddStockOutScreen> createState() => _AddStockOutScreenState();
}

class _AddStockOutScreenState extends State<AddStockOutScreen> {
  final ApiClient _apiClient = ApiClient();
  final _formKey = GlobalKey<FormState>();

  final _receiverController = TextEditingController();
  final _noteController = TextEditingController();

  String _stockOutType = 'Radio'; // Default type: Radio (serialized) or Accessory (qty)
  
  XFile? _selectedImage;
  final ImagePicker _picker = ImagePicker();

  List<Map<String, dynamic>> _models = [];
  bool _isLoadingModels = true;
  String? _modelsError;

  // Selected radio items: { "model_id": int, "serial_number": string }
  final List<Map<String, dynamic>> _radioItems = [];

  // Selected accessory items: { "model_id": int, "quantity": int, "note": string }
  final List<Map<String, dynamic>> _accessoryItems = [];

  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _fetchModels();
    _addItemLine(); // Add one initial line
  }

  @override
  void dispose() {
    _receiverController.dispose();
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
      if (_stockOutType == 'Radio') {
        _radioItems.add({
          'model_id': null,
          'serial_number': '',
          'controller': TextEditingController(),
        });
      } else {
        _accessoryItems.add({
          'model_id': null,
          'quantity': 1,
          'note': '',
        });
      }
    });
  }

  void _removeItemLine(int index) {
    setState(() {
      if (_stockOutType == 'Radio') {
        if (_radioItems.length > 1) {
          _radioItems[index]['controller']?.dispose();
          _radioItems.removeAt(index);
        }
      } else {
        if (_accessoryItems.length > 1) {
          _accessoryItems.removeAt(index);
        }
      }
    });
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

  // Opens a camera scanner and populates the text field
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

    // Validation check
    if (_stockOutType == 'Radio') {
      for (var item in _radioItems) {
        if (item['model_id'] == null || item['serial_number'].toString().trim().isEmpty) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('សូមជ្រើសរើសម៉ូដែល និងវាយលេខស៊េរីវិទ្យុទាក់ទង / Please select model and enter S/N')),
          );
          return;
        }
      }
    } else {
      for (var item in _accessoryItems) {
        if (item['model_id'] == null) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('សូមជ្រើសរើសម៉ូដែលគ្រឿងបន្លាស់ / Please select accessory model')),
          );
          return;
        }
      }
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      Map<String, dynamic> payload = {
        'receiver': _receiverController.text,
        'type': _stockOutType,
        'note': _noteController.text,
      };

      if (_stockOutType == 'Radio') {
        payload['items'] = [];
        for (int i = 0; i < _radioItems.length; i++) {
          payload['items[$i][model_id]'] = _radioItems[i]['model_id'];
          payload['items[$i][serial_number]'] = _radioItems[i]['serial_number'];
        }
      } else {
        payload['accessories'] = [];
        for (int i = 0; i < _accessoryItems.length; i++) {
          payload['accessories[$i][model_id]'] = _accessoryItems[i]['model_id'];
          payload['accessories[$i][quantity]'] = _accessoryItems[i]['quantity'];
          payload['accessories[$i][note]'] = _accessoryItems[i]['note'];
        }
        // Laravel controller needs 'items' array even if accessory to pass validation
        payload['items'] = []; // empty
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
      final response = await _apiClient.dio.post('/stock-outs', data: formData);

      if (response.statusCode == 210 || (response.statusCode == 200 && response.data['success'])) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('នាំចេញស្តុកជោគជ័យ / Stock Out recorded successfully')),
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
    
    // Filter models based on selection type
    final filteredModels = _models.where((m) {
      final isAccessory = m['accessory'] == true || m['accessory'] == 1 || m['accessory'] == '1';
      return _stockOutType == 'Radio' ? !isAccessory : isAccessory;
    }).toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('នាំចេញស្តុកថ្មី / New Stock Out'),
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
                        // Receiver & Type Card
                        Card(
                          elevation: 1,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          child: Padding(
                            padding: const EdgeInsets.all(16.0),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'ព័ត៌មានការនាំចេញ / Stock Out Info',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                ),
                                const SizedBox(height: 16),
                                TextFormField(
                                  controller: _receiverController,
                                  decoration: const InputDecoration(
                                    labelText: 'អ្នកទទួល / Receiver',
                                    border: OutlineInputBorder(),
                                    prefixIcon: Icon(Icons.person),
                                  ),
                                  validator: (value) {
                                    if (value == null || value.trim().isEmpty) {
                                      return 'សូមបញ្ចូលឈ្មោះអ្នកទទួល / Please enter receiver name';
                                    }
                                    return null;
                                  },
                                ),
                                const SizedBox(height: 16),
                                // Type Radio toggle
                                Row(
                                  children: [
                                    const Text('ប្រភេទឧបករណ៍ / Type:'),
                                    const SizedBox(width: 20),
                                    Radio<String>(
                                      value: 'Radio',
                                      groupValue: _stockOutType,
                                      onChanged: (val) {
                                        setState(() {
                                          _stockOutType = val!;
                                          _radioItems.clear();
                                          _accessoryItems.clear();
                                          _addItemLine();
                                        });
                                      },
                                    ),
                                    const Text('វិទ្យុ (Radio)'),
                                    const SizedBox(width: 10),
                                    Radio<String>(
                                      value: 'Accessory',
                                      groupValue: _stockOutType,
                                      onChanged: (val) {
                                        setState(() {
                                          _stockOutType = val!;
                                          _radioItems.clear();
                                          _accessoryItems.clear();
                                          _addItemLine();
                                        });
                                      },
                                    ),
                                    const Text('គ្រឿងបន្លាស់'),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Image Picker
                        const Text(
                          'រូបភាពភស្តុតាង ឬហត្ថលេខា / Proof or Signature Photo',
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
                                      Text('បន្ថែមរូបថតអ្នកទទួល / Add Receiver Photo', style: TextStyle(color: Colors.grey)),
                                    ],
                                  ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // List Section Title
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              _stockOutType == 'Radio'
                                  ? 'បញ្ជីវិទ្យុនាំចេញ / Radios to Stock Out'
                                  : 'បញ្ជីគ្រឿងបន្លាស់ / Accessories to Stock Out',
                              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                            TextButton.icon(
                              onPressed: _addItemLine,
                              icon: const Icon(Icons.add),
                              label: const Text('Add Row'),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),

                        // Radio Items List
                        if (_stockOutType == 'Radio')
                          ListView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: _radioItems.length,
                            itemBuilder: (context, index) {
                              final item = _radioItems[index];
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
                                          Expanded(
                                            child: DropdownButtonFormField<int>(
                                              value: item['model_id'],
                                              decoration: const InputDecoration(labelText: 'ម៉ូដែល / Model'),
                                              items: filteredModels.map((m) {
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
                                            onPressed: () => _removeItemLine(index),
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
                          )
                        else
                          // Accessory Items List
                          ListView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: _accessoryItems.length,
                            itemBuilder: (context, index) {
                              final item = _accessoryItems[index];
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
                                          Expanded(
                                            child: DropdownButtonFormField<int>(
                                              value: item['model_id'],
                                              decoration: const InputDecoration(labelText: 'គ្រឿងបន្លាស់ / Accessory'),
                                              items: filteredModels.map((m) {
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
                                            onPressed: () => _removeItemLine(index),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 8),
                                      TextFormField(
                                        initialValue: item['note'],
                                        decoration: const InputDecoration(labelText: 'កំណត់ចំណាំគ្រឿងបន្លាស់ / Note'),
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
                                    'រក្សាទុកការនាំចេញ / Submit Stock Out',
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
