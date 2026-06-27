import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../../core/network/api_client.dart';
import '../../../core/constants/constants.dart';

class FrequencyListScreen extends StatefulWidget {
  const FrequencyListScreen({super.key});

  @override
  State<FrequencyListScreen> createState() => _FrequencyListScreenState();
}

class _FrequencyListScreenState extends State<FrequencyListScreen> {
  final ApiClient _apiClient = ApiClient();
  List<dynamic> _frequencies = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetchFrequencyData();
  }

  Future<void> _fetchFrequencyData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await _apiClient.dio.get('/set-frequencies');
      if (response.statusCode == 200 && response.data['success']) {
        setState(() {
          _frequencies = response.data['data']['data'] ?? [];
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = 'Failed to load frequency settings';
          _isLoading = false;
        });
      }
    } on DioException catch (e) {
      setState(() {
        _error = e.response?.data['message'] ?? 'Unable to connect to the server';
        _isLoading = false;
      });
    } catch (_) {
      setState(() {
        _error = 'An unexpected error occurred';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;

    return Scaffold(
      appBar: AppBar(
        title: const Text('កំណត់ប្រេកង់ / Set Frequencies'),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _fetchFrequencyData,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24.0),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline, size: 64, color: Colors.red.shade700),
                        const SizedBox(height: 16),
                        Text(_error!, style: const TextStyle(fontSize: 16), textAlign: TextAlign.center),
                        const SizedBox(height: 24),
                        ElevatedButton(
                          onPressed: _fetchFrequencyData,
                          child: const Text('Try Again'),
                        ),
                      ],
                    ),
                  ),
                )
              : _frequencies.isEmpty
                  ? const Center(child: Text('មិនមានទិន្នន័យប្រេកង់ទេ / No frequency data'))
                  : RefreshIndicator(
                      onRefresh: _fetchFrequencyData,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _frequencies.length,
                        itemBuilder: (context, index) {
                          final item = _frequencies[index];
                          final imageUrl = AppConstants.formatImageUrl(item['image']);

                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            elevation: 1,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            child: InkWell(
                              borderRadius: BorderRadius.circular(12),
                              onTap: () => _showFrequencyDetails(item),
                              child: Padding(
                                padding: const EdgeInsets.all(16.0),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    if (imageUrl != null) ...[
                                      Container(
                                        width: 60,
                                        height: 60,
                                        margin: const EdgeInsets.only(right: 16),
                                        decoration: BoxDecoration(
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: Colors.grey.shade200),
                                        ),
                                        child: ClipRRect(
                                          borderRadius: BorderRadius.circular(8),
                                          child: Image.network(
                                            imageUrl,
                                            fit: BoxFit.cover,
                                            errorBuilder: (_, __, ___) => const Icon(Icons.broken_image, color: Colors.grey),
                                          ),
                                        ),
                                      ),
                                    ],
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                            children: [
                                              Expanded(
                                                child: Text(
                                                  'Name: ${item['name']}',
                                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                                ),
                                              ),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                                decoration: BoxDecoration(
                                                  color: Colors.blue.shade50,
                                                  borderRadius: BorderRadius.circular(6),
                                                ),
                                                child: Text(
                                                  item['trimester'] ?? 'N/A',
                                                  style: TextStyle(
                                                    color: primaryColor,
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 11,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 8),
                                          Text('Unit: ${item['unit']}'),
                                          Text('Purpose: ${item['purpose']}'),
                                          Text('Date of Setup: ${item['date_of_setup'] ?? "N/A"}'),
                                          const Divider(height: 20),
                                          Row(
                                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                            children: [
                                              Text(
                                                'Setup by: ${item['user']?['name'] ?? "Unknown"}',
                                                style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
                                              ),
                                              Text(
                                                '${item['detail']?.length ?? 0} Radios',
                                                style: TextStyle(color: primaryColor, fontWeight: FontWeight.bold),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
    );
  }

  void _showFrequencyDetails(Map<String, dynamic> item) {
    final primaryColor = Colors.blue.shade800;
    final imageUrl = AppConstants.formatImageUrl(item['image']);
    final details = item['detail'] as List<dynamic>? ?? [];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return DraggableScrollableSheet(
          initialChildSize: 0.6,
          minChildSize: 0.4,
          maxChildSize: 0.9,
          expand: false,
          builder: (context, scrollController) {
            return SingleChildScrollView(
              controller: scrollController,
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 50,
                      height: 5,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  Text(
                    item['name'] ?? 'Frequency Setup',
                    style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Trimester: ${item['trimester'] ?? "N/A"}',
                    style: TextStyle(color: primaryColor, fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                  const Divider(height: 30),
                  
                  if (imageUrl != null) ...[
                    const Text('គំនូសបំពាក់ / Setup Image:', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Container(
                      width: double.infinity,
                      height: 180,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey.shade300),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.network(imageUrl, fit: BoxFit.cover),
                      ),
                    ),
                    const SizedBox(height: 20),
                  ],

                  const Text('ព័ត៌មានលម្អិត / Details:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 8),
                  _buildDetailRow('Unit / អង្គភាព:', item['unit'] ?? 'N/A'),
                  _buildDetailRow('Purpose / គោលបំណង:', item['purpose'] ?? 'N/A'),
                  _buildDetailRow('Date of Setup:', item['date_of_setup'] ?? 'N/A'),
                  _buildDetailRow('Created By:', item['user']?['name'] ?? 'Unknown'),
                  const Divider(height: 30),

                  Text('បញ្ជីវិទ្យុទាក់ទង (${details.length}) / Radios List:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 8),
                  if (details.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 20),
                      child: Center(child: Text('គ្មានវិទ្យុដែលត្រូវបានកំណត់ប្រេកង់នេះទេ / No radios assigned')),
                    )
                  else
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: details.length,
                      itemBuilder: (context, idx) {
                        final det = details[idx];
                        final product = det['product'];
                        final model = product?['model'];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          child: ListTile(
                            leading: Icon(Icons.radio, color: primaryColor),
                            title: Text(
                              model?['name'] ?? 'Unknown Model',
                              style: const TextStyle(fontWeight: FontWeight.bold),
                            ),
                            subtitle: Text('S/N: ${product?['PID'] ?? "N/A"}'),
                            trailing: Text(model?['brand']?['brand_name'] ?? model?['brand_name'] ?? 'Radio'),
                          ),
                        );
                      },
                    ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 140, child: Text(label, style: TextStyle(color: Colors.grey.shade600))),
          Expanded(child: Text(value, style: const TextStyle(fontWeight: FontWeight.bold))),
        ],
      ),
    );
  }
}
