import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../../core/network/api_client.dart';
import 'borrow_detail_screen.dart';
import 'add_borrow_screen.dart';
import '../../widgets/profile_dropdown_action.dart';

class BorrowManagementScreen extends StatefulWidget {
  const BorrowManagementScreen({super.key});

  @override
  State<BorrowManagementScreen> createState() => _BorrowManagementScreenState();
}

class _BorrowManagementScreenState extends State<BorrowManagementScreen> {
  final ApiClient _apiClient = ApiClient();
  List<dynamic> _borrows = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetchBorrowData();
  }

  Future<void> _fetchBorrowData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await _apiClient.dio.get('/borrows');
      if (response.statusCode == 200 && response.data['success']) {
        setState(() {
          _borrows = response.data['data']['data'] ?? [];
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = 'Failed to load borrowing records';
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
        title: const Text('ការខ្ចីឧបករណ៍ / Borrowing Records'),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _fetchBorrowData,
          ),
          const ProfileDropdownAction(),
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
                          onPressed: _fetchBorrowData,
                          child: const Text('Try Again'),
                        ),
                      ],
                    ),
                  ),
                )
              : _borrows.isEmpty
                  ? const Center(child: Text('មិនមានប្រវត្តិខ្ចីឧបករណ៍ទេ / No borrowing history'))
                  : RefreshIndicator(
                      onRefresh: _fetchBorrowData,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _borrows.length,
                        itemBuilder: (context, index) {
                          final item = _borrows[index];
                          final isBorrowed = item['borrowed'] == 1 || item['borrowed'] == '1';

                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            elevation: 1,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            child: InkWell(
                              borderRadius: BorderRadius.circular(12),
                              onTap: () async {
                                await Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => BorrowDetailScreen(item: item),
                                  ),
                                );
                                _fetchBorrowData();
                              },
                              child: Padding(
                                padding: const EdgeInsets.all(16.0),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          'Receiver: ${item['receiver']}',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: isBorrowed ? Colors.red.shade50 : Colors.green.shade50,
                                            borderRadius: BorderRadius.circular(6),
                                          ),
                                          child: Text(
                                            isBorrowed ? 'កំពុងខ្ចី / Borrowed' : 'បានប្រគល់ / Returned',
                                            style: TextStyle(
                                              color: isBorrowed ? Colors.red.shade700 : Colors.green.shade700,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 11,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    Text('Purpose: ${item['purpose']}'),
                                    if (item['note'] != null && item['note'].isNotEmpty)
                                      Text('Note: ${item['note']}', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                                    const Divider(height: 20),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          'Date: ${item['created_at'] != null ? item['created_at'].substring(0, 10) : "N/A"}',
                                          style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
                                        ),
                                        Text(
                                          '${(item['details']?.length ?? 0) + (item['accessory']?.length ?? 0)} items',
                                          style: TextStyle(color: primaryColor, fontWeight: FontWeight.bold),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const AddBorrowScreen(),
            ),
          );
          if (result == true) {
            _fetchBorrowData();
          }
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
