import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../../core/constants/constants.dart';
import '../../../core/network/api_client.dart';

class BorrowDetailScreen extends StatefulWidget {
  final Map<String, dynamic> item;

  const BorrowDetailScreen({super.key, required this.item});

  @override
  State<BorrowDetailScreen> createState() => _BorrowDetailScreenState();
}

class _BorrowDetailScreenState extends State<BorrowDetailScreen> {
  final ApiClient _apiClient = ApiClient();
  late Map<String, dynamic> _item;
  bool _isSubmittingReturn = false;

  @override
  void initState() {
    super.initState();
    _item = widget.item;
  }

  // Refetches this specific borrow record to update the UI after a return
  Future<void> _refreshDetails() async {
    try {
      final response = await _apiClient.dio.get('/borrows');
      if (response.statusCode == 200 && response.data['success']) {
        final List<dynamic> list = response.data['data']['data'] ?? [];
        final updated = list.firstWhere((x) => x['id'] == _item['id'], orElse: () => null);
        if (updated != null) {
          setState(() {
            _item = updated;
          });
        }
      }
    } catch (_) {}
  }

  void _showReturnDialog() {
    final formKey = GlobalKey<FormState>();
    final returnerController = TextEditingController(text: _item['receiver']);
    final noteController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return AlertDialog(
              title: const Text('ប្រគល់ឧបករណ៍វិញ / Return Items'),
              content: Form(
                key: formKey,
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      TextFormField(
                        controller: returnerController,
                        decoration: const InputDecoration(
                          labelText: 'ឈ្មោះអ្នកប្រគល់ / Returner Name',
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
                      TextFormField(
                        controller: noteController,
                        decoration: const InputDecoration(
                          labelText: 'កំណត់ចំណាំ / Note',
                          border: OutlineInputBorder(),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                TextButton(
                  onPressed: _isSubmittingReturn ? null : () => Navigator.pop(context),
                  child: const Text('Cancel'),
                ),
                ElevatedButton(
                  onPressed: _isSubmittingReturn
                      ? null
                      : () async {
                          if (!formKey.currentState!.validate()) return;
                          
                          setModalState(() {
                            _isSubmittingReturn = true;
                          });

                          try {
                            // Collect items being returned
                            final List<Map<String, dynamic>> returnItems = [];
                            final List<dynamic> details = _item['details'] as List<dynamic>? ?? [];
                            for (var d in details) {
                              if (d['borrowed'] == 1 || d['borrowed'] == '1') {
                                returnItems.add({
                                  'serial_number': d['product']?['PID'],
                                });
                              }
                            }

                            final List<Map<String, dynamic>> returnAccessories = [];
                            final List<dynamic> accessories = _item['accessory'] as List<dynamic>? ?? [];
                            for (var a in accessories) {
                              if (a['borrowed'] == 1 || a['borrowed'] == '1') {
                                returnAccessories.add({
                                  'model_id': a['model_id'],
                                  'quantity': a['quantity'],
                                });
                              }
                            }

                            final payload = {
                              'returner_name': returnerController.text,
                              'note': noteController.text,
                              'items': returnItems,
                              'accessories': returnAccessories,
                            };

                            final response = await _apiClient.dio.post(
                              '/borrows/${_item['id']}/return',
                              data: payload,
                            );

                            if (response.statusCode == 200 && response.data['success']) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('ប្រគល់ឧបករណ៍ជោគជ័យ / Returned successfully')),
                              );
                              Navigator.pop(context); // Close dialog
                              _refreshDetails();      // Refresh local state
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
                            setModalState(() {
                              _isSubmittingReturn = false;
                            });
                          }
                        },
                  child: _isSubmittingReturn
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Return All'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;
    final imageUrl = AppConstants.formatImageUrl(_item['image']);
    
    final details = _item['details'] as List<dynamic>? ?? [];
    final accessories = _item['accessory'] as List<dynamic>? ?? [];
    
    final isBorrowed = _item['borrowed'] == 1 || _item['borrowed'] == '1';

    return Scaffold(
      appBar: AppBar(
        title: Text('Borrow Details #${_item['id']}'),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status Card
            Card(
              color: isBorrowed ? Colors.red.shade50 : Colors.green.shade50,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                child: Row(
                  children: [
                    Icon(
                      isBorrowed ? Icons.error_outline : Icons.check_circle_outline,
                      color: isBorrowed ? Colors.red.shade700 : Colors.green.shade700,
                    ),
                    const SizedBox(width: 12),
                    Text(
                      isBorrowed ? 'កំពុងខ្ចី / Borrowed' : 'បានប្រគល់រួចរាល់ / Returned',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: isBorrowed ? Colors.red.shade700 : Colors.green.shade700,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // General Info Card
            Card(
              elevation: 1,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'ព័ត៌មានទូទៅ / General Information',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                    const Divider(height: 24),
                    _buildInfoRow('Receiver / អ្នកខ្ចី:', _item['receiver'] ?? 'N/A'),
                    _buildInfoRow('Purpose / គោលបំណង:', _item['purpose'] ?? 'N/A'),
                    _buildInfoRow('Date / កាលបរិច្ឆេទ:', _item['created_at'] != null ? _item['created_at'].substring(0, 10) : 'N/A'),
                    _buildInfoRow('Note / កំណត់ចំណាំ:', _item['note'] ?? 'No notes.'),
                    if (_item['log'] != null) _buildInfoRow('Logs / ប្រវត្តិប្រតិបត្តិការ:', _item['log']!),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Attached Image if any
            if (imageUrl != null) ...[
              const Text(
                'រូបភាពឯកសារភ្ជាប់ / Attached Image',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              GestureDetector(
                onTap: () => _showFullImage(context, imageUrl),
                child: Container(
                  width: double.infinity,
                  height: 200,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(12),
                    color: Colors.grey.shade100,
                    border: Border.all(color: Colors.grey.shade300),
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Image.network(
                      imageUrl,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => const Center(
                        child: Icon(Icons.broken_image, size: 50, color: Colors.grey),
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 20),
            ],

            // Radios List
            if (details.isNotEmpty) ...[
              const Text(
                'វិទ្យុទាក់ទង / Radios List',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.blue),
              ),
              const SizedBox(height: 8),
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: details.length,
                itemBuilder: (context, index) {
                  final d = details[index];
                  final product = d['product'];
                  final model = product?['model'];
                  final isItemBorrowed = d['borrowed'] == 1 || d['borrowed'] == '1';
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    child: ListTile(
                      leading: Icon(Icons.radio, color: isItemBorrowed ? Colors.red : Colors.green),
                      title: Text(
                        model?['name'] ?? 'Unknown Model',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text('S/N: ${product?['PID'] ?? "N/A"}'),
                      trailing: Chip(
                        label: Text(isItemBorrowed ? 'Borrowed' : 'Returned', style: const TextStyle(fontSize: 11)),
                        backgroundColor: isItemBorrowed ? Colors.red.shade50 : Colors.green.shade50,
                        labelStyle: TextStyle(
                          color: isItemBorrowed ? Colors.red.shade700 : Colors.green.shade700,
                        ),
                      ),
                    ),
                  );
                },
              ),
              const SizedBox(height: 16),
            ],

            // Accessories List
            if (accessories.isNotEmpty) ...[
              const Text(
                'គ្រឿងបន្លាស់ / Accessories List',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.orange),
              ),
              const SizedBox(height: 8),
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: accessories.length,
                itemBuilder: (context, index) {
                  final a = accessories[index];
                  final model = a['model'];
                  final isItemBorrowed = a['borrowed'] == 1 || a['borrowed'] == '1';
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    child: ListTile(
                      leading: Icon(Icons.electrical_services, color: isItemBorrowed ? Colors.red : Colors.green),
                      title: Text(
                        model?['name'] ?? 'Unknown Model',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text('Quantity: ${a['quantity']}'),
                      trailing: Chip(
                        label: Text(isItemBorrowed ? 'Borrowed' : 'Returned', style: const TextStyle(fontSize: 11)),
                        backgroundColor: isItemBorrowed ? Colors.red.shade50 : Colors.green.shade50,
                        labelStyle: TextStyle(
                          color: isItemBorrowed ? Colors.red.shade700 : Colors.green.shade700,
                        ),
                      ),
                    ),
                  );
                },
              ),
              const SizedBox(height: 24),
            ],

            // Return Button
            if (isBorrowed)
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton.icon(
                  onPressed: _showReturnDialog,
                  icon: const Icon(Icons.keyboard_return),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.red.shade700,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  label: const Text(
                    'ប្រគល់ឧបករណ៍ទាំងអស់វិញ / Return All Items',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  void _showFullImage(BuildContext context, String url) {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Stack(
              alignment: Alignment.topRight,
              children: [
                Image.network(url, fit: BoxFit.contain),
                IconButton(
                  icon: const Icon(Icons.close, color: Colors.white, size: 30),
                  onPressed: () => Navigator.pop(context),
                  style: IconButton.styleFrom(backgroundColor: Colors.black54),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 150,
            child: Text(
              label,
              style: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.w500),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}
