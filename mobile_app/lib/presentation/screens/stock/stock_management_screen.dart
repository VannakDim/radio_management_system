import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../../bloc/auth_bloc.dart';
import '../../bloc/auth_state.dart';
import 'stock_in_detail_screen.dart';
import 'stock_out_detail_screen.dart';
import 'add_stock_in_screen.dart';
import 'add_stock_out_screen.dart';
import '../../widgets/profile_dropdown_action.dart';

class StockManagementScreen extends StatefulWidget {
  const StockManagementScreen({super.key});

  @override
  State<StockManagementScreen> createState() => _StockManagementScreenState();
}

class _StockManagementScreenState extends State<StockManagementScreen> with SingleTickerProviderStateMixin {
  final ApiClient _apiClient = ApiClient();
  late TabController _tabController;
  
  List<dynamic> _stockIns = [];
  List<dynamic> _stockOuts = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _tabController.addListener(() {
      if (!mounted) return;
      setState(() {});
    });
    _fetchStockData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchStockData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final inResponse = await _apiClient.dio.get('/stock-ins');
      final outResponse = await _apiClient.dio.get('/stock-outs');

      if (inResponse.statusCode == 200 && outResponse.statusCode == 200) {
        setState(() {
          _stockIns = inResponse.data['data']['data'] ?? [];
          _stockOuts = outResponse.data['data']['data'] ?? [];
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = 'Failed to load stock data';
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

    // Get current user ID from BLoC
    final authState = context.watch<AuthBloc>().state;
    final int? currentUserId = authState is Authenticated ? authState.user.id : null;

    return Scaffold(
      appBar: AppBar(
        title: const Text('គ្រប់គ្រងស្តុក / Stock Management'),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        bottom: TabBar(
          controller: _tabController,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          indicatorColor: Colors.white,
          tabs: const [
            Tab(text: 'នាំចូល / Stock In'),
            Tab(text: 'នាំចេញ / Stock Out'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _fetchStockData,
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
                          onPressed: _fetchStockData,
                          child: const Text('Try Again'),
                        ),
                      ],
                    ),
                  ),
                )
              : TabBarView(
                  controller: _tabController,
                  children: [
                    _buildStockInList(currentUserId),
                    _buildStockOutList(currentUserId),
                  ],
                ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => _tabController.index == 0
                  ? const AddStockInScreen()
                  : const AddStockOutScreen(),
            ),
          );
          if (result == true) {
            _fetchStockData();
          }
        },
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildStockInList(int? currentUserId) {
    if (_stockIns.isEmpty) {
      return const Center(child: Text('មិនមានទិន្នន័យនាំចូលទេ / No Stock In data'));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _stockIns.length,
      itemBuilder: (context, index) {
        final item = _stockIns[index];
        final isOwner = currentUserId != null && item['user_id'] == currentUserId;
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          elevation: 1,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: InkWell(
            borderRadius: BorderRadius.circular(12),
            onTap: () async {
              final result = await Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => StockInDetailScreen(item: item, currentUserId: currentUserId),
                ),
              );
              if (result == true) _fetchStockData();
            },
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: Colors.green.shade50,
                child: Icon(Icons.arrow_downward, color: Colors.green.shade700),
              ),
              title: Text(
                item['invoice_no'] != null ? 'Invoice: ${item['invoice_no']}' : 'Stock In #${item['id']}',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              subtitle: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Supplier: ${item['supplier'] ?? "N/A"}'),
                  Text('Date: ${item['created_at'] != null ? item['created_at'].substring(0, 10) : "N/A"}'),
                ],
              ),
              trailing: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (isOwner)
                    Icon(Icons.edit_note, size: 18, color: Colors.blue.shade400),
                  const SizedBox(width: 4),
                  Chip(
                    label: Text(
                      '${item['detail']?.length ?? 0} items',
                      style: const TextStyle(fontSize: 12),
                    ),
                    backgroundColor: Colors.grey.shade100,
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildStockOutList(int? currentUserId) {
    if (_stockOuts.isEmpty) {
      return const Center(child: Text('មិនមានទិន្នន័យនាំចេញទេ / No Stock Out data'));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _stockOuts.length,
      itemBuilder: (context, index) {
        final item = _stockOuts[index];
        final isOwner = currentUserId != null && item['user_id'] == currentUserId;
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          elevation: 1,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: InkWell(
            borderRadius: BorderRadius.circular(12),
            onTap: () async {
              final result = await Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => StockOutDetailScreen(item: item, currentUserId: currentUserId),
                ),
              );
              if (result == true) _fetchStockData();
            },
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: Colors.orange.shade50,
                child: Icon(Icons.arrow_upward, color: Colors.orange.shade700),
              ),
              title: Text(
                'Receiver: ${item['receiver'] ?? "Unknown"}',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              subtitle: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Purpose: ${item['type'] ?? "N/A"}'),
                  Text('Date: ${item['created_at'] != null ? item['created_at'].substring(0, 10) : "N/A"}'),
                ],
              ),
              trailing: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (isOwner)
                    Icon(Icons.edit_note, size: 18, color: Colors.blue.shade400),
                  const SizedBox(width: 4),
                  Chip(
                    label: Text(
                      '${(item['products']?.length ?? 0) + (item['stock_out_details']?.length ?? 0)} items',
                      style: const TextStyle(fontSize: 12),
                    ),
                    backgroundColor: Colors.grey.shade100,
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}
