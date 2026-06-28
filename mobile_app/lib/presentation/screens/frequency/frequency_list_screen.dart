import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../../core/network/api_client.dart';
import '../../../core/constants/constants.dart';
import '../utils/image_editor_screen.dart';
import 'add_frequency_screen.dart';
import '../../widgets/profile_dropdown_action.dart';

class FrequencyListScreen extends StatefulWidget {
  const FrequencyListScreen({super.key});

  @override
  State<FrequencyListScreen> createState() => _FrequencyListScreenState();
}

class _FrequencyListScreenState extends State<FrequencyListScreen> with SingleTickerProviderStateMixin {
  final ApiClient _apiClient = ApiClient();
  late TabController _tabController;

  List<dynamic> _frequencies = [];
  List<String> _trimesters = [];
  String? _selectedTrimesterString;
  String? _selectedYear;
  String? _selectedTrimester;
  List<dynamic> _unitStats = [];

  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _fetchFrequencyData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchFrequencyData({String? trimester}) async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final queryParams = <String, dynamic>{};
      if (trimester != null) {
        queryParams['trimester'] = trimester;
      }

      final response = await _apiClient.dio.get('/set-frequencies', queryParameters: queryParams);
      if (response.statusCode == 200 && response.data['success']) {
        final trimestersList = List<String>.from(response.data['trimesters'] ?? []);
        final currentTrimester = response.data['selected_trimester'] as String?;

        setState(() {
          _frequencies = response.data['data']['data'] ?? [];
          _trimesters = trimestersList;
          _selectedTrimesterString = currentTrimester;
          _unitStats = response.data['unit_stats'] ?? [];

          if (currentTrimester != null && currentTrimester.contains('-')) {
            final parts = currentTrimester.split('-');
            _selectedYear = parts[0];
            _selectedTrimester = parts[1];
          }
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

  List<String> get _years {
    final years = _trimesters.map((t) {
      if (t.contains('-')) {
        return t.split('-')[0];
      }
      return '';
    }).where((y) => y.isNotEmpty).toSet().toList();
    years.sort((a, b) => b.compareTo(a)); // Descending order
    return years;
  }

  Widget _buildFilterCard() {
    final yearsList = _years;
    final trimestersList = ['T1', 'T2', 'T3', 'T4'];

    if (_selectedYear == null && yearsList.isNotEmpty) {
      _selectedYear = yearsList.first;
    }
    if (_selectedTrimester == null) {
      _selectedTrimester = 'T1';
    }

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: Colors.grey.shade200),
      ),
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Row(
          children: [
            Expanded(
              child: DropdownButtonFormField<String>(
                value: _selectedYear,
                decoration: const InputDecoration(
                  labelText: 'ឆ្នាំ / Year',
                  contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                  border: OutlineInputBorder(),
                ),
                items: yearsList.map((y) {
                  return DropdownMenuItem<String>(
                    value: y,
                    child: Text(y),
                  );
                }).toList(),
                onChanged: (val) {
                  if (val != null) {
                    setState(() {
                      _selectedYear = val;
                    });
                    _fetchFrequencyData(trimester: '$_selectedYear-$_selectedTrimester');
                  }
                },
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: DropdownButtonFormField<String>(
                value: _selectedTrimester,
                decoration: const InputDecoration(
                  labelText: 'ត្រីមាស / Trimester',
                  contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                  border: OutlineInputBorder(),
                ),
                items: trimestersList.map((t) {
                  final title = t == 'T1'
                      ? 'T1 (ត្រីមាស ១)'
                      : t == 'T2'
                          ? 'T2 (ត្រីមាស ២)'
                          : t == 'T3'
                              ? 'T3 (ត្រីមាស ៣)'
                              : 'T4 (ត្រីមាស ៤)';
                  return DropdownMenuItem<String>(
                    value: t,
                    child: Text(title, style: const TextStyle(fontSize: 12)),
                  );
                }).toList(),
                onChanged: (val) {
                  if (val != null) {
                    setState(() {
                      _selectedTrimester = val;
                    });
                    _fetchFrequencyData(trimester: '$_selectedYear-$_selectedTrimester');
                  }
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFrequencyList() {
    if (_frequencies.isEmpty) {
      return const Center(child: Text('មិនមានទិន្នន័យប្រេកង់ទេ / No frequency data'));
    }

    final primaryColor = Colors.blue.shade800;

    return RefreshIndicator(
      onRefresh: () => _fetchFrequencyData(trimester: '$_selectedYear-$_selectedTrimester'),
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
                    Container(
                      width: 60,
                      height: 60,
                      margin: const EdgeInsets.only(right: 16),
                      decoration: BoxDecoration(
                        color: imageUrl == null ? Colors.blue.shade50 : Colors.white,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: imageUrl == null ? Colors.blue.shade200 : Colors.grey.shade200,
                          width: imageUrl == null ? 1.5 : 1.0,
                        ),
                      ),
                      child: imageUrl != null
                          ? ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.network(
                                imageUrl,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => const Icon(Icons.broken_image, color: Colors.grey),
                              ),
                            )
                          : InkWell(
                              onTap: () => _updateRecordImage(context, item['id']),
                              borderRadius: BorderRadius.circular(8),
                              child: Icon(Icons.add_a_photo, size: 22, color: Colors.blue.shade800),
                            ),
                    ),
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
    );
  }

  Widget _buildUnitStatsList() {
    if (_unitStats.isEmpty) {
      return const Center(child: Text('មិនមានទិន្នន័យស្ថិតិទេ / No statistics data'));
    }

    return RefreshIndicator(
      onRefresh: () => _fetchFrequencyData(trimester: '$_selectedYear-$_selectedTrimester'),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _unitStats.length,
        itemBuilder: (context, index) {
          final stat = _unitStats[index];
          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            elevation: 1,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: BorderSide(color: Colors.blue.shade50),
            ),
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          stat['unit_name'] ?? 'Unknown Unit',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'ត្រីមាស៖ $_selectedTrimesterString',
                          style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                  Row(
                    children: [
                      _buildStatBadge(
                        'ការដំឡើង / Setups',
                        '${stat['setups_count']}',
                        Colors.blue,
                      ),
                      const SizedBox(width: 12),
                      _buildStatBadge(
                        'វិទ្យុទាក់ទង / Radios',
                        '${stat['radios_count']}',
                        Colors.green,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildStatBadge(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: color.withOpacity(0.08),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Column(
        children: [
          Text(
            value,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: color.withOpacity(0.9),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label.split(' / ')[0],
            style: TextStyle(
              fontSize: 9,
              color: Colors.grey.shade700,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
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
            onPressed: () => _fetchFrequencyData(trimester: '$_selectedYear-$_selectedTrimester'),
          ),
          const ProfileDropdownAction(),
        ],
        bottom: TabBar(
          controller: _tabController,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          indicatorColor: Colors.white,
          tabs: const [
            Tab(text: 'បញ្ជីប្រេកង់ / Frequency List'),
            Tab(text: 'ស្ថិតិតាមអង្គភាព / Unit Stats'),
          ],
        ),
      ),
      body: Column(
        children: [
          _buildFilterCard(),
          Expanded(
            child: _isLoading
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
                                onPressed: () => _fetchFrequencyData(trimester: '$_selectedYear-$_selectedTrimester'),
                                child: const Text('Try Again'),
                              ),
                            ],
                          ),
                        ),
                      )
                    : TabBarView(
                        controller: _tabController,
                        children: [
                          _buildFrequencyList(),
                          _buildUnitStatsList(),
                        ],
                      ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        child: const Icon(Icons.add),
        onPressed: () async {
          final result = await Navigator.push<bool>(
            context,
            MaterialPageRoute(
              builder: (context) => const AddFrequencyScreen(),
            ),
          );
          if (result == true) {
            _fetchFrequencyData(
              trimester: _selectedYear != null && _selectedTrimester != null
                  ? '$_selectedYear-$_selectedTrimester'
                  : null,
            );
          }
        },
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

                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('គំនូសបំពាក់ / Setup Image:', style: TextStyle(fontWeight: FontWeight.bold)),
                      if (imageUrl != null)
                        TextButton.icon(
                          onPressed: () => _updateRecordImage(context, item['id']),
                          icon: const Icon(Icons.edit, size: 16),
                          label: const Text('ផ្លាស់ប្តូរ / Change', style: TextStyle(fontSize: 12)),
                        ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (imageUrl != null) ...[
                    GestureDetector(
                      onTap: () => _showFullImage(context, imageUrl),
                      child: Container(
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
                    ),
                    const SizedBox(height: 20),
                  ] else ...[
                    InkWell(
                      onTap: () => _updateRecordImage(context, item['id']),
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        width: double.infinity,
                        height: 140,
                        decoration: BoxDecoration(
                          color: Colors.grey.shade50,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: Colors.blue.shade200,
                            style: BorderStyle.solid,
                            width: 1.5,
                          ),
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.add_a_photo_outlined, size: 40, color: primaryColor),
                            const SizedBox(height: 8),
                            Text(
                              'មិនទាន់មានរូបភាពទេ / No image set',
                              style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'ចុចទីនេះដើម្បីបញ្ចូលរូបភាព / Tap to add image',
                              style: TextStyle(color: primaryColor, fontWeight: FontWeight.bold, fontSize: 12),
                            ),
                          ],
                        ),
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

  void _showFullImage(BuildContext context, String url) {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(16),
        child: Stack(
          alignment: Alignment.topRight,
          children: [
            InteractiveViewer(
              maxScale: 4.0,
              child: Center(
                child: Image.network(
                  url,
                  fit: BoxFit.contain,
                ),
              ),
            ),
            Positioned(
              top: 16,
              right: 16,
              child: IconButton(
                icon: const Icon(Icons.close, color: Colors.white, size: 30),
                onPressed: () => Navigator.pop(context),
                style: IconButton.styleFrom(backgroundColor: Colors.black54),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _updateRecordImage(BuildContext context, int id) async {
    try {
      final ImagePicker picker = ImagePicker();
      final XFile? image = await picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 80,
        maxWidth: 1200,
        maxHeight: 1200,
      );
      if (image != null) {
        final XFile? edited = await Navigator.push<XFile>(
          context,
          MaterialPageRoute(
            builder: (context) => ImageEditorScreen(imageFile: image),
          ),
        );
        if (edited != null && context.mounted) {
          // Show progress indicator
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (context) => const Center(child: CircularProgressIndicator()),
          );

          final payload = <String, dynamic>{};
          if (kIsWeb) {
            final bytes = await edited.readAsBytes();
            payload['image'] = MultipartFile.fromBytes(bytes, filename: edited.name);
          } else {
            payload['image'] = await MultipartFile.fromFile(edited.path, filename: edited.name);
          }

          final response = await _apiClient.dio.post(
            '/set-frequencies/$id/update-image',
            data: FormData.fromMap(payload),
          );

          // Close progress dialog
          Navigator.pop(context);

          if (response.statusCode == 200 && response.data['success']) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('ធ្វើបច្ចុប្បន្នភាពរូបភាពជោគជ័យ / Image updated successfully')),
            );
            Navigator.pop(context); // Close details modal sheet
            _fetchFrequencyData(
              trimester: _selectedYear != null && _selectedTrimester != null
                  ? '$_selectedYear-$_selectedTrimester'
                  : null,
            ); // Reload
          } else {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text('Failed: ${response.data['message']}')),
            );
          }
        }
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error updating image: $e')),
      );
    }
  }
}
