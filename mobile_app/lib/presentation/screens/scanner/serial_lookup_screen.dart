import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../../core/network/api_client.dart';

class SerialLookupScreen extends StatefulWidget {
  const SerialLookupScreen({super.key});

  @override
  State<SerialLookupScreen> createState() => _SerialLookupScreenState();
}

class _SerialLookupScreenState extends State<SerialLookupScreen> {
  final ApiClient _apiClient = ApiClient();
  final _searchController = TextEditingController();
  
  bool _isScanning = false;
  bool _isSearching = false;
  Map<String, dynamic>? _result;
  String? _message;

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _searchSerial(String serialNumber) async {
    if (serialNumber.trim().isEmpty) return;

    setState(() {
      _isSearching = true;
      _result = null;
      _message = null;
    });

    try {
      final response = await _apiClient.dio.post('/products/check-serial', data: {
        'serial_number': serialNumber.trim(),
      });

      if (response.statusCode == 200 && response.data['success']) {
        if (response.data['exists']) {
          setState(() {
            _result = response.data['data'];
            _isSearching = false;
          });
        } else {
          setState(() {
            _message = 'មិនរកឃើញឧបករណ៍នេះក្នុងប្រព័ន្ធទេ / Device not found in system';
            _isSearching = false;
          });
        }
      }
    } on DioException catch (e) {
      setState(() {
        _message = e.response?.data['message'] ?? 'Error communicating with server';
        _isSearching = false;
      });
    } catch (_) {
      setState(() {
        _message = 'An unexpected error occurred';
        _isSearching = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;

    return Scaffold(
      appBar: AppBar(
        title: const Text('ស្វែងរកឧបករណ៍ / Device Lookup'),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Search Input Row
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: 'បញ្ចូលលេខ PID / Enter Serial Number',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                ElevatedButton(
                  onPressed: () => _searchSerial(_searchController.text),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: const Icon(Icons.search),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Scan QR/Barcode Trigger
            ElevatedButton.icon(
              onPressed: () {
                setState(() {
                  _isScanning = !_isScanning;
                  _result = null;
                  _message = null;
                });
              },
              icon: Icon(_isScanning ? Icons.stop : Icons.qr_code_scanner),
              label: Text(
                _isScanning ? 'បិទកាមេរ៉ា / Stop Scanner' : 'ស្កេន QR / Barcode Scan',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: _isScanning ? Colors.red.shade700 : Colors.teal.shade700,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Camera Scanner Widget
            if (_isScanning) ...[
              Container(
                height: 250,
                decoration: BoxDecoration(
                  border: Border.all(color: primaryColor, width: 2),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(14),
                  child: MobileScanner(
                    onDetect: (capture) {
                      final List<Barcode> barcodes = capture.barcodes;
                      for (final barcode in barcodes) {
                        if (barcode.rawValue != null) {
                          final code = barcode.rawValue!;
                          _searchController.text = code;
                          setState(() {
                            _isScanning = false;
                          });
                          _searchSerial(code);
                          break;
                        }
                      }
                    },
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Loader
            if (_isSearching)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(24.0),
                  child: CircularProgressIndicator(),
                ),
              ),

            // Error / Message View
            if (_message != null)
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.amber.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.amber.shade300),
                ),
                child: Row(
                  children: [
                    Icon(Icons.info_outline, color: Colors.amber.shade800),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        _message!,
                        style: TextStyle(color: Colors.amber.shade900, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
              ),

            // Result Card
            if (_result != null) ...[
              Card(
                elevation: 3,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.check_circle, color: Colors.green.shade600, size: 28),
                          const SizedBox(width: 8),
                          Text(
                            'រកឃើញឧបករណ៍! / Device Found!',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Colors.green.shade800,
                            ),
                          ),
                        ],
                      ),
                      const Divider(height: 24),
                      _buildDetailRow('លេខសម្គាល់ / PID:', _result!['serial_number']),
                      _buildDetailRow('ម៉ូដែល / Model:', _result!['model_name']),
                      _buildDetailRow('ប្រភេទ / Type:', _result!['accessory'] ? 'គ្រឿងបន្លាស់ / Accessory' : 'ម៉ាស៊ីនវិទ្យុ / Device'),
                    ],
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 140,
            child: Text(
              label,
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.grey.shade700,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                color: Colors.grey.shade900,
                fontSize: 15,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
