import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../../core/network/api_client.dart';
import '../utils/image_editor_screen.dart';

class AddFrequencyScreen extends StatefulWidget {
  const AddFrequencyScreen({super.key});

  @override
  State<AddFrequencyScreen> createState() => _AddFrequencyScreenState();
}

class _AddFrequencyScreenState extends State<AddFrequencyScreen> {
  final ApiClient _apiClient = ApiClient();
  final _formKey = GlobalKey<FormState>();

  final _nameController = TextEditingController();
  final _purposeController = TextEditingController();
  final _dateController = TextEditingController();

  int? _selectedUnitId;
  String? _selectedUnitName;
  String _selectedTrimester = '1'; // Default: Trimester 1
  XFile? _selectedImage;

  List<Map<String, dynamic>> _items = [
    {'model_id': null, 'serial_number': '', 'controller': TextEditingController()}
  ];

  List<dynamic> _units = [];
  List<dynamic> _models = [];
  bool _isLoadingUnits = true;
  bool _isLoadingModels = true;
  bool _isSubmitting = false;

  String? _unitsError;
  String? _modelsError;

  final ImagePicker _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    _dateController.text = DateTime.now().toString().substring(0, 10);
    _fetchUnits();
    _fetchModels();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _purposeController.dispose();
    _dateController.dispose();
    for (var item in _items) {
      item['controller']?.dispose();
    }
    super.dispose();
  }

  Future<void> _fetchUnits() async {
    try {
      final response = await _apiClient.dio.get('/units');
      if (response.statusCode == 200 && response.data['success']) {
        setState(() {
          _units = response.data['data'] ?? [];
          _isLoadingUnits = false;
        });
      } else {
        setState(() {
          _unitsError = 'Failed to load units';
          _isLoadingUnits = false;
        });
      }
    } catch (_) {
      setState(() {
        _unitsError = 'Error loading units';
        _isLoadingUnits = false;
      });
    }
  }

  Future<void> _fetchModels() async {
    try {
      final response = await _apiClient.dio.get('/product-models');
      if (response.statusCode == 200 && response.data['success']) {
        setState(() {
          _models = response.data['data'] ?? [];
          _isLoadingModels = false;
        });
      } else {
        setState(() {
          _modelsError = 'Failed to load models';
          _isLoadingModels = false;
        });
      }
    } catch (_) {
      setState(() {
        _modelsError = 'Error loading models';
        _isLoadingModels = false;
      });
    }
  }

  void _addItemLine() {
    setState(() {
      _items.add({
        'model_id': null,
        'serial_number': '',
        'controller': TextEditingController(),
      });
    });
  }

  void _removeItemLine(int index) {
    if (_items.length > 1) {
      setState(() {
        _items[index]['controller']?.dispose();
        _items.removeAt(index);
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('ត្រូវតែមានវិទ្យុទាក់ទងយ៉ាងតិច ១ / Must have at least 1 radio')),
      );
    }
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
                          _items[index]['serial_number'] = code;
                          _items[index]['controller']?.text = code;
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

  Future<void> _selectDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
    );
    if (picked != null) {
      setState(() {
        _dateController.text = picked.toString().substring(0, 10);
      });
    }
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedUnitId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('សូមជ្រើសរើសអង្គភាព / Please select a unit')),
      );
      return;
    }

    // Validate that all items have model and serial number
    for (var item in _items) {
      if (item['model_id'] == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('សូមជ្រើសរើសម៉ូដែលវិទ្យុទាក់ទង / Please select a model for all rows')),
        );
        return;
      }
      if (item['serial_number'].toString().trim().isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('សូមបញ្ចូលលេខស៊េរី / Please enter a serial number for all rows')),
        );
        return;
      }
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      Map<String, dynamic> payload = {
        'name': _nameController.text.trim(),
        'unit': _selectedUnitName,
        'unit_id': _selectedUnitId,
        'purpose': _purposeController.text.trim(),
        'trimester': _selectedTrimester,
        'setup_date': _dateController.text,
      };

      for (int i = 0; i < _items.length; i++) {
        payload['items[$i][model_id]'] = _items[i]['model_id'];
        payload['items[$i][serial_number]'] = _items[i]['serial_number'].toString().trim();
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
      final response = await _apiClient.dio.post('/set-frequencies', data: formData);

      if (response.statusCode == 210 || (response.statusCode == 200 && response.data['success'])) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('កំណត់ប្រេកង់ជោគជ័យ / Frequency configured successfully')),
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

    return Scaffold(
      appBar: AppBar(
        title: const Text('កំណត់ប្រេកង់ថ្មី / New Frequency Setup'),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
      ),
      body: _isLoadingUnits || _isLoadingModels
          ? const Center(child: CircularProgressIndicator())
          : _unitsError != null || _modelsError != null
              ? Center(child: Text(_unitsError ?? _modelsError ?? 'Error loading data'))
              : Form(
                  key: _formKey,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // Config Card
                      Card(
                        elevation: 1,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'ព័ត៌មានទូទៅ / General Information',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: primaryColor),
                              ),
                              const SizedBox(height: 16),
                              // Setup Name
                              TextFormField(
                                controller: _nameController,
                                decoration: const InputDecoration(
                                  labelText: 'ឈ្មោះការរៀបចំ / Setup Name',
                                  prefixIcon: Icon(Icons.settings_input_antenna),
                                  border: OutlineInputBorder(),
                                ),
                                validator: (val) {
                                  if (val == null || val.trim().isEmpty) {
                                    return 'សូមបញ្ចូលឈ្មោះ / Please enter setup name';
                                  }
                                  return null;
                                },
                              ),
                              const SizedBox(height: 16),
                              // Unit Dropdown
                              DropdownButtonFormField<int>(
                                value: _selectedUnitId,
                                decoration: const InputDecoration(
                                  labelText: 'អង្គភាព / Organization Unit',
                                  prefixIcon: Icon(Icons.business),
                                  border: OutlineInputBorder(),
                                ),
                                items: _units.map((unit) {
                                  return DropdownMenuItem<int>(
                                    value: unit['id'],
                                    child: Text(unit['name'] ?? ''),
                                  );
                                }).toList(),
                                onChanged: (val) {
                                  final match = _units.firstWhere((u) => u['id'] == val);
                                  setState(() {
                                    _selectedUnitId = val;
                                    _selectedUnitName = match['name'];
                                  });
                                },
                              ),
                              const SizedBox(height: 16),
                              // Purpose
                              TextFormField(
                                controller: _purposeController,
                                decoration: const InputDecoration(
                                  labelText: 'គោលបំណង / Purpose',
                                  prefixIcon: Icon(Icons.description_outlined),
                                  border: OutlineInputBorder(),
                                ),
                                validator: (val) {
                                  if (val == null || val.trim().isEmpty) {
                                    return 'Required';
                                  }
                                  return null;
                                },
                              ),
                              const SizedBox(height: 16),
                              Row(
                                children: [
                                  // Trimester Dropdown
                                  Expanded(
                                    child: DropdownButtonFormField<String>(
                                      value: _selectedTrimester,
                                      decoration: const InputDecoration(
                                        labelText: 'ត្រីមាស / Trimester',
                                        prefixIcon: Icon(Icons.calendar_today),
                                        border: OutlineInputBorder(),
                                      ),
                                      items: const [
                                        DropdownMenuItem(value: '1', child: Text('ត្រីមាស ១ (T1)')),
                                        DropdownMenuItem(value: '2', child: Text('ត្រីមាស ២ (T2)')),
                                        DropdownMenuItem(value: '3', child: Text('ត្រីមាស ៣ (T3)')),
                                        DropdownMenuItem(value: '4', child: Text('ត្រីមាស ៤ (T4)')),
                                      ],
                                      onChanged: (val) {
                                        if (val != null) {
                                          setState(() {
                                            _selectedTrimester = val;
                                          });
                                        }
                                      },
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  // Setup Date
                                  Expanded(
                                    child: TextFormField(
                                      controller: _dateController,
                                      readOnly: true,
                                      onTap: _selectDate,
                                      decoration: const InputDecoration(
                                        labelText: 'កាលបរិច្ឆេទ / Date',
                                        prefixIcon: Icon(Icons.date_range),
                                        border: OutlineInputBorder(),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 20),
                              // Image Picker Section
                              Text(
                                'គំនូសបំពាក់ប្រេកង់ / Setup Image',
                                style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey.shade700),
                              ),
                              const SizedBox(height: 8),
                              InkWell(
                                onTap: _pickImage,
                                borderRadius: BorderRadius.circular(8),
                                child: Container(
                                  width: double.infinity,
                                  height: 140,
                                  decoration: BoxDecoration(
                                    color: Colors.grey.shade100,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(color: Colors.grey.shade300),
                                  ),
                                  child: _selectedImage == null
                                      ? Column(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Icon(Icons.photo_library_outlined, size: 36, color: Colors.grey.shade500),
                                            const SizedBox(height: 8),
                                            const Text('រើសរូបភាពគំនូសបំពាក់ / Select Image (Auto crop support)'),
                                          ],
                                        )
                                      : ClipRRect(
                                          borderRadius: BorderRadius.circular(8),
                                          child: kIsWeb
                                              ? Image.network(_selectedImage!.path, fit: BoxFit.cover)
                                              : Image.file(File(_selectedImage!.path), fit: BoxFit.cover),
                                        ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      // List of Radios (Products) Section
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'បញ្ជីវិទ្យុទាក់ទង / Radios List',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: primaryColor),
                          ),
                          TextButton.icon(
                            onPressed: _addItemLine,
                            icon: const Icon(Icons.add),
                            label: const Text('បន្ថែមវិទ្យុ / Add Radio'),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _items.length,
                        itemBuilder: (context, index) {
                          final item = _items[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            elevation: 1,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                              side: BorderSide(color: Colors.grey.shade200),
                            ),
                            child: Padding(
                              padding: const EdgeInsets.all(12.0),
                              child: Column(
                                children: [
                                  Row(
                                    children: [
                                      // Model Selection
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
                                      // Remove button
                                      IconButton(
                                        icon: const Icon(Icons.delete_outline, color: Colors.red),
                                        onPressed: () => _removeItemLine(index),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      // Serial Number input
                                      Expanded(
                                        child: TextFormField(
                                          controller: item['controller'],
                                          decoration: const InputDecoration(
                                            labelText: 'លេខស៊េរី (S/N PID) / Serial Number',
                                            contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                          ),
                                          onChanged: (val) {
                                            item['serial_number'] = val;
                                          },
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      // Barcode scanner trigger
                                      IconButton(
                                        icon: Icon(Icons.qr_code_scanner, color: primaryColor),
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
                      const SizedBox(height: 24),
                      ElevatedButton(
                        onPressed: _isSubmitting ? null : _submitForm,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryColor,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: _isSubmitting
                            ? const CircularProgressIndicator(color: Colors.white)
                            : const Text('បង្កើតការកំណត់ប្រេកង់ / CREATE SETUP', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      ),
                      const SizedBox(height: 40),
                    ],
                  ),
                ),
    );
  }
}
