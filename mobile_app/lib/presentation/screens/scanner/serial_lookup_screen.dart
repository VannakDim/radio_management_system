import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../../core/network/api_client.dart';

class SerialLookupScreen extends StatefulWidget {
  const SerialLookupScreen({super.key});

  @override
  State<SerialLookupScreen> createState() => _SerialLookupScreenState();
}

class _SerialLookupScreenState extends State<SerialLookupScreen> with SingleTickerProviderStateMixin {
  final ApiClient _apiClient = ApiClient();
  final _searchController = TextEditingController();
  final _globalSearchController = TextEditingController();
  late TabController _tabController;
  
  bool _isScanning = false;
  bool _isSearching = false;
  Map<String, dynamic>? _result;
  String? _message;

  bool _isGlobalSearching = false;
  Map<String, List<dynamic>> _globalResults = {
    'owners': [],
    'products': [],
    'frequencies': [],
    'stock_outs': [],
  };
  String? _globalError;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _globalSearchController.dispose();
    _tabController.dispose();
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

  Future<void> _runGlobalSearch(String query) async {
    if (query.trim().length < 2) return;

    setState(() {
      _isGlobalSearching = true;
      _globalError = null;
    });

    try {
      final response = await _apiClient.dio.get('/search/global', queryParameters: {
        'q': query.trim(),
      });

      if (response.statusCode == 200 && response.data['success']) {
        final data = response.data['data'];
        setState(() {
          _globalResults = {
            'owners': List<dynamic>.from(data['owners'] ?? []),
            'products': List<dynamic>.from(data['products'] ?? []),
            'frequencies': List<dynamic>.from(data['frequencies'] ?? []),
            'stock_outs': List<dynamic>.from(data['stock_outs'] ?? []),
          };
          _isGlobalSearching = false;
        });
      }
    } on DioException catch (e) {
      setState(() {
        _globalError = e.response?.data['message'] ?? 'Error communicating with server';
        _isGlobalSearching = false;
      });
    } catch (_) {
      setState(() {
        _globalError = 'An unexpected error occurred';
        _isGlobalSearching = false;
      });
    }
  }

  void _showResultDetailDialog(Map<String, dynamic> item) {
    showDialog(
      context: context,
      builder: (context) {
        final type = item['type'];
        final data = item['data'];

        List<Widget> detailRows = [];
        if (type == 'Product') {
          detailRows = [
            _buildDetailRow('ប្រភេទ / Type:', data['accessory'] ? 'គ្រឿងបន្លាស់ / Accessory' : 'ម៉ាស៊ីនវិទ្យុ / Device'),
            _buildDetailRow('ម៉ូដែល / Model:', data['model_name'] ?? 'Unknown'),
            _buildDetailRow('លេខសម្គាល់ / PID:', data['serial_number'] ?? ''),
          ];
        } else if (type == 'Frequency') {
          final List<dynamic> pids = data['pids'] ?? [];
          detailRows = [
            _buildDetailRow('ឈ្មោះ / Name:', data['name'] ?? ''),
            _buildDetailRow('អង្គភាព / Unit:', data['unit'] ?? ''),
            _buildDetailRow('គោលបំណង / Purpose:', data['purpose'] ?? ''),
            _buildDetailRow('ត្រីមាស / Trimester:', data['trimester'] ?? ''),
            _buildDetailRow('លេខសម្គាល់ឧបករណ៍ / Device PIDs:', pids.isNotEmpty ? pids.join(', ') : 'គ្មាន / None'),
          ];
        } else if (type == 'Borrow') {
          detailRows = [
            _buildDetailRow('អ្នកខ្ចី / Receiver:', data['receiver'] ?? ''),
            _buildDetailRow('គោលបំណង / Purpose:', data['purpose'] ?? ''),
            _buildDetailRow('សម្គាល់ / Note:', data['note'] ?? 'គ្មាន / None'),
            _buildDetailRow('ស្ថានភាព / Status:', data['returned'] ? 'បានប្រគល់មកវិញ / Returned' : 'កំពុងខ្ចី / Borrowing'),
          ];
        } else if (type == 'Owner') {
          final List<dynamic> pids = data['pids'] ?? [];
          detailRows = [
            _buildDetailRow('ឈ្មោះ / Name:', data['name'] ?? ''),
            _buildDetailRow('លេខទូរស័ព្ទ / Phone:', data['phone'] ?? ''),
            _buildDetailRow('លេខសម្គាល់ឧបករណ៍ / Device PIDs:', pids.isNotEmpty ? pids.join(', ') : 'គ្មាន / None'),
          ];
        } else if (type == 'StockOut') {
          final List<dynamic> pids = data['pids'] ?? [];
          detailRows = [
            _buildDetailRow('អ្នកទទួល / Receiver:', data['receiver'] ?? ''),
            _buildDetailRow('ប្រភេទ / Type:', data['type'] ?? ''),
            _buildDetailRow('សម្គាល់ / Note:', data['note'] ?? 'គ្មាន / None'),
            _buildDetailRow('លេខសម្គាល់ឧបករណ៍ / Device PIDs:', pids.isNotEmpty ? pids.join(', ') : 'គ្មាន / None'),
          ];
        }

        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Row(
            children: [
              Icon(
                type == 'Product'
                    ? Icons.radio
                    : type == 'Frequency'
                        ? Icons.settings_input_antenna
                        : type == 'Owner'
                            ? Icons.person
                            : type == 'StockOut'
                                ? Icons.unarchive
                                : Icons.assignment,
                color: Colors.blue.shade800,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  type == 'Product'
                      ? 'ព័ត៌មានឧបករណ៍ / Device Info'
                      : type == 'Frequency'
                          ? 'ការកំណត់ប្រេកង់ / Frequency Setup'
                          : type == 'Owner'
                              ? 'ព័ត៌មានម្ចាស់ / Owner Info'
                              : type == 'StockOut'
                                  ? 'ប័ណ្ណបញ្ចេញឧបករណ៍ / Stock Out Info'
                                  : 'កិច្ចសន្យាខ្ចីឧបករណ៍ / Borrow Contract',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Divider(),
              const SizedBox(height: 8),
              ...detailRows,
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('បិទ / Close'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;

    return Scaffold(
      appBar: AppBar(
        title: const Text('ស្វែងរកឧបករណ៍ / Device Lookup'),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.orange,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(
              icon: Icon(Icons.qr_code_scanner),
              text: 'ស្កេន & ឆែកស៊េរី / Scan & Lookup',
            ),
            Tab(
              icon: Icon(Icons.travel_explore),
              text: 'ស្វែងរកទូទៅ / Global Search',
            ),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // Tab 1: Scan & Lookup Serial
          SingleChildScrollView(
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
          
          // Tab 2: Global Search
          SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Global Search Input Row
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _globalSearchController,
                        onSubmitted: (val) => _runGlobalSearch(val),
                        decoration: InputDecoration(
                          hintText: 'ស្វែងរកអ្វីក៏បាន... / Search anything...',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          prefixIcon: const Icon(Icons.search),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton(
                      onPressed: () => _runGlobalSearch(_globalSearchController.text),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: primaryColor,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: const Text('ស្វែងរក'),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                if (_isGlobalSearching)
                  const Center(
                    child: Padding(
                      padding: EdgeInsets.all(24.0),
                      child: CircularProgressIndicator(),
                    ),
                  ),

                if (_globalError != null) ...[
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.red.shade50,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.red.shade200),
                    ),
                    child: Text(
                      _globalError!,
                      style: TextStyle(color: Colors.red.shade800, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(height: 16),
                ],

                // Display Results
                if (!_isGlobalSearching) ...[
                  // 1. Owners Section
                  if (_globalResults['owners'] != null && _globalResults['owners']!.isNotEmpty) ...[
                    _buildSectionHeader('ម្ចាស់ឧបករណ៍ / Owners', Icons.person),
                    ..._globalResults['owners']!.map((item) => _buildResultCard(item)),
                    const SizedBox(height: 16),
                  ],

                  // 2. Products Section
                  if (_globalResults['products'] != null && _globalResults['products']!.isNotEmpty) ...[
                    _buildSectionHeader('វិទ្យុ & គ្រឿងបន្លាស់ / Radios & Accessories', Icons.radio),
                    ..._globalResults['products']!.map((item) => _buildResultCard(item)),
                    const SizedBox(height: 16),
                  ],

                  // 3. Frequencies Section
                  if (_globalResults['frequencies'] != null && _globalResults['frequencies']!.isNotEmpty) ...[
                    _buildSectionHeader('ការរៀបចំប្រេកង់ / Frequencies', Icons.settings_input_antenna),
                    ..._globalResults['frequencies']!.map((item) => _buildResultCard(item)),
                    const SizedBox(height: 16),
                  ],

                  // 4. Stock Outs Section
                  if (_globalResults['stock_outs'] != null && _globalResults['stock_outs']!.isNotEmpty) ...[
                    _buildSectionHeader('បញ្ចេញទំនិញ / Stock Outs', Icons.unarchive),
                    ..._globalResults['stock_outs']!.map((item) => _buildResultCard(item)),
                    const SizedBox(height: 16),
                  ],

                  // No Results found message
                  if (_globalSearchController.text.isNotEmpty &&
                      _globalResults['owners']!.isEmpty &&
                      _globalResults['products']!.isEmpty &&
                      _globalResults['frequencies']!.isEmpty &&
                      _globalResults['stock_outs']!.isEmpty)
                    Center(
                      child: Padding(
                        padding: const EdgeInsets.all(32.0),
                        child: Column(
                          children: [
                            Icon(Icons.search_off, size: 48, color: Colors.grey.shade400),
                            const SizedBox(height: 8),
                            Text(
                              'មិនមានទិន្នន័យស្របគ្នាទេ / No matching results',
                              style: TextStyle(color: Colors.grey.shade500, fontSize: 16),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
              ],
            ),
          ),
        ],
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

  Widget _buildSectionHeader(String title, IconData icon) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Icon(icon, color: Colors.orange.shade700, size: 20),
          const SizedBox(width: 8),
          Text(
            title,
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.bold,
              color: Colors.blue.shade900,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildResultCard(Map<String, dynamic> item) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 4.0),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      child: ListTile(
        leading: Icon(
          item['type'] == 'Product'
              ? Icons.radio
              : item['type'] == 'Frequency'
                  ? Icons.settings_input_antenna
                  : item['type'] == 'Owner'
                      ? Icons.person
                      : item['type'] == 'StockOut'
                          ? Icons.unarchive
                          : Icons.assignment,
          color: Colors.blue.shade600,
        ),
        title: Text(
          item['title'] ?? '',
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
        ),
        subtitle: Text(
          item['subtitle'] ?? '',
          style: const TextStyle(fontSize: 12),
        ),
        trailing: Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          decoration: BoxDecoration(
            color: Colors.blue.shade50,
            borderRadius: BorderRadius.circular(6),
          ),
          child: Text(
            item['badge'] ?? '',
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.bold,
              color: Colors.blue.shade700,
            ),
          ),
        ),
        onTap: () => _showResultDetailDialog(item),
      ),
    );
  }
}
