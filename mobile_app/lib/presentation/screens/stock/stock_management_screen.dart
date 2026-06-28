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

class _StockManagementScreenState extends State<StockManagementScreen>
    with SingleTickerProviderStateMixin {
  final ApiClient _apiClient = ApiClient();
  late TabController _tabController;

  // ─── Stock In pagination state ───────────────────────────────────────────
  final List<dynamic> _stockIns = [];
  int _inCurrentPage = 1;
  int _inLastPage = 1;
  bool _inLoading = true;
  bool _inLoadingMore = false;
  String? _inError;
  final ScrollController _inScrollController = ScrollController();

  // ─── Stock Out pagination state ──────────────────────────────────────────
  final List<dynamic> _stockOuts = [];
  int _outCurrentPage = 1;
  int _outLastPage = 1;
  bool _outLoading = true;
  bool _outLoadingMore = false;
  String? _outError;
  final ScrollController _outScrollController = ScrollController();

  @override
  void initState() {
    super.initState();

    _tabController = TabController(length: 2, vsync: this);
    _tabController.addListener(() {
      if (!mounted) return;
      setState(() {});
    });

    // Listen for scroll-to-bottom on each tab list
    _inScrollController.addListener(_onInScroll);
    _outScrollController.addListener(_onOutScroll);

    // Initial load
    _fetchStockIns(reset: true);
    _fetchStockOuts(reset: true);
  }

  @override
  void dispose() {
    _tabController.dispose();
    _inScrollController.dispose();
    _outScrollController.dispose();
    super.dispose();
  }

  // ── Scroll listeners ──────────────────────────────────────────────────────

  void _onInScroll() {
    if (_inScrollController.position.pixels >=
            _inScrollController.position.maxScrollExtent - 200 &&
        !_inLoadingMore &&
        _inCurrentPage < _inLastPage) {
      _fetchStockIns();
    }
  }

  void _onOutScroll() {
    if (_outScrollController.position.pixels >=
            _outScrollController.position.maxScrollExtent - 200 &&
        !_outLoadingMore &&
        _outCurrentPage < _outLastPage) {
      _fetchStockOuts();
    }
  }

  // ── Fetch Stock Ins ───────────────────────────────────────────────────────

  Future<void> _fetchStockIns({bool reset = false}) async {
    if (reset) {
      setState(() {
        _stockIns.clear();
        _inCurrentPage = 1;
        _inLastPage = 1;
        _inLoading = true;
        _inError = null;
      });
    } else {
      if (_inLoadingMore) return;
      setState(() => _inLoadingMore = true);
    }

    final page = reset ? 1 : _inCurrentPage + 1;

    try {
      final response = await _apiClient.dio.get('/stock-ins', queryParameters: {'page': page, 'per_page': 15});
      if (!mounted) return;

      final paginationData = response.data['data'];
      final List<dynamic> newItems = paginationData['data'] ?? [];

      setState(() {
        _stockIns.addAll(newItems);
        // Sort LIFO: newest created_at first
        _stockIns.sort((a, b) {
          final aDate = DateTime.tryParse(a['created_at'] ?? '') ?? DateTime(0);
          final bDate = DateTime.tryParse(b['created_at'] ?? '') ?? DateTime(0);
          return bDate.compareTo(aDate);
        });
        _inCurrentPage = paginationData['current_page'] ?? page;
        _inLastPage = paginationData['last_page'] ?? 1;
        _inLoading = false;
        _inLoadingMore = false;
      });
    } on DioException catch (e) {
      if (!mounted) return;
      setState(() {
        _inError = e.response?.data['message'] ?? 'Unable to connect';
        _inLoading = false;
        _inLoadingMore = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _inError = 'An unexpected error occurred';
        _inLoading = false;
        _inLoadingMore = false;
      });
    }
  }

  // ── Fetch Stock Outs ──────────────────────────────────────────────────────

  Future<void> _fetchStockOuts({bool reset = false}) async {
    if (reset) {
      setState(() {
        _stockOuts.clear();
        _outCurrentPage = 1;
        _outLastPage = 1;
        _outLoading = true;
        _outError = null;
      });
    } else {
      if (_outLoadingMore) return;
      setState(() => _outLoadingMore = true);
    }

    final page = reset ? 1 : _outCurrentPage + 1;

    try {
      final response = await _apiClient.dio.get('/stock-outs', queryParameters: {'page': page, 'per_page': 15});
      if (!mounted) return;

      final paginationData = response.data['data'];
      final List<dynamic> newItems = paginationData['data'] ?? [];

      setState(() {
        _stockOuts.addAll(newItems);
        // Sort LIFO: newest created_at first
        _stockOuts.sort((a, b) {
          final aDate = DateTime.tryParse(a['created_at'] ?? '') ?? DateTime(0);
          final bDate = DateTime.tryParse(b['created_at'] ?? '') ?? DateTime(0);
          return bDate.compareTo(aDate);
        });
        _outCurrentPage = paginationData['current_page'] ?? page;
        _outLastPage = paginationData['last_page'] ?? 1;
        _outLoading = false;
        _outLoadingMore = false;
      });
    } on DioException catch (e) {
      if (!mounted) return;
      setState(() {
        _outError = e.response?.data['message'] ?? 'Unable to connect';
        _outLoading = false;
        _outLoadingMore = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _outError = 'An unexpected error occurred';
        _outLoading = false;
        _outLoadingMore = false;
      });
    }
  }

  // ── Refresh both tabs ─────────────────────────────────────────────────────

  Future<void> _refreshAll() async {
    await Future.wait([
      _fetchStockIns(reset: true),
      _fetchStockOuts(reset: true),
    ]);
  }

  // ─────────────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;
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
          tabs: [
            Tab(text: 'នាំចូល / Stock In${_inLastPage > 1 ? " (p$_inCurrentPage/$_inLastPage)" : ""}'),
            Tab(text: 'នាំចេញ / Stock Out${_outLastPage > 1 ? " (p$_outCurrentPage/$_outLastPage)" : ""}'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _refreshAll,
          ),
          const ProfileDropdownAction(),
        ],
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildStockInTab(currentUserId),
          _buildStockOutTab(currentUserId),
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
          if (result == true) _refreshAll();
        },
        child: const Icon(Icons.add),
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────────────────
  // Stock In Tab
  // ─────────────────────────────────────────────────────────────────────────

  Widget _buildStockInTab(int? currentUserId) {
    if (_inLoading) return const Center(child: CircularProgressIndicator());

    if (_inError != null && _stockIns.isEmpty) {
      return _buildErrorView(_inError!, () => _fetchStockIns(reset: true));
    }

    if (_stockIns.isEmpty) {
      return const Center(child: Text('មិនមានទិន្នន័យនាំចូលទេ / No Stock In data'));
    }

    return RefreshIndicator(
      onRefresh: () => _fetchStockIns(reset: true),
      child: ListView.builder(
        controller: _inScrollController,
        padding: const EdgeInsets.all(16),
        // +1 for the loading indicator / end-of-list footer
        itemCount: _stockIns.length + 1,
        itemBuilder: (context, index) {
          // Footer
          if (index == _stockIns.length) {
            return _buildListFooter(_inLoadingMore, _inCurrentPage, _inLastPage);
          }

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
                    builder: (context) =>
                        StockInDetailScreen(item: item, currentUserId: currentUserId),
                  ),
                );
                if (result == true) _fetchStockIns(reset: true);
              },
              child: ListTile(
                leading: CircleAvatar(
                  backgroundColor: Colors.green.shade50,
                  child: Icon(Icons.arrow_downward, color: Colors.green.shade700),
                ),
                title: Text(
                  item['invoice_no'] != null
                      ? 'Invoice: ${item['invoice_no']}'
                      : 'Stock In #${item['id']}',
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
                    if (isOwner) Icon(Icons.edit_note, size: 18, color: Colors.blue.shade400),
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
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────────────────
  // Stock Out Tab
  // ─────────────────────────────────────────────────────────────────────────

  Widget _buildStockOutTab(int? currentUserId) {
    if (_outLoading) return const Center(child: CircularProgressIndicator());

    if (_outError != null && _stockOuts.isEmpty) {
      return _buildErrorView(_outError!, () => _fetchStockOuts(reset: true));
    }

    if (_stockOuts.isEmpty) {
      return const Center(child: Text('មិនមានទិន្នន័យនាំចេញទេ / No Stock Out data'));
    }

    return RefreshIndicator(
      onRefresh: () => _fetchStockOuts(reset: true),
      child: ListView.builder(
        controller: _outScrollController,
        padding: const EdgeInsets.all(16),
        itemCount: _stockOuts.length + 1,
        itemBuilder: (context, index) {
          // Footer
          if (index == _stockOuts.length) {
            return _buildListFooter(_outLoadingMore, _outCurrentPage, _outLastPage);
          }

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
                    builder: (context) =>
                        StockOutDetailScreen(item: item, currentUserId: currentUserId),
                  ),
                );
                if (result == true) _fetchStockOuts(reset: true);
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
                    if (isOwner) Icon(Icons.edit_note, size: 18, color: Colors.blue.shade400),
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
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────────────────
  // Shared helper widgets
  // ─────────────────────────────────────────────────────────────────────────

  /// Shows a spinner while loading more, or an "end of list" indicator.
  Widget _buildListFooter(bool isLoadingMore, int currentPage, int lastPage) {
    if (isLoadingMore) {
      return const Padding(
        padding: EdgeInsets.symmetric(vertical: 20),
        child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
      );
    }
    if (currentPage >= lastPage) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 20),
        child: Center(
          child: Text(
            '─── ទិន្នន័យទាំងអស់ត្រូវបានទាញយករួចហើយ ───',
            style: TextStyle(color: Colors.grey.shade400, fontSize: 12),
          ),
        ),
      );
    }
    return const SizedBox.shrink();
  }

  Widget _buildErrorView(String error, VoidCallback onRetry) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 64, color: Colors.red.shade700),
            const SizedBox(height: 16),
            Text(error, style: const TextStyle(fontSize: 16), textAlign: TextAlign.center),
            const SizedBox(height: 24),
            ElevatedButton(onPressed: onRetry, child: const Text('ព្យាយាមម្ដងទៀត / Try Again')),
          ],
        ),
      ),
    );
  }
}
