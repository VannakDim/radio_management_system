import 'package:flutter/material.dart';
import '../../../core/constants/constants.dart';

class StockInDetailScreen extends StatelessWidget {
  final Map<String, dynamic> item;

  const StockInDetailScreen({super.key, required this.item});

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;
    final imageUrl = AppConstants.formatImageUrl(item['image']);
    final details = item['detail'] as List<dynamic>? ?? [];

    return Scaffold(
      appBar: AppBar(
        title: Text(item['invoice_no'] != null ? 'Invoice: ${item['invoice_no']}' : 'Stock In #${item['id']}'),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
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
                    _buildInfoRow('Invoice No:', item['invoice_no'] ?? 'N/A'),
                    _buildInfoRow('Supplier / ក្រុមហ៊ុនផ្គត់ផ្គង់:', item['supplier'] ?? 'N/A'),
                    _buildInfoRow('Date / កាលបរិច្ឆេទ:', item['created_at'] != null ? item['created_at'].substring(0, 10) : 'N/A'),
                    _buildInfoRow('Recorded by / បញ្ចូលដោយ:', item['user']?['name'] ?? 'Unknown'),
                    _buildInfoRow('Note / កំណត់សម្គាល់:', item['note'] ?? 'No notes.'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Document Image if any
            if (imageUrl != null) ...[
              const Text(
                'រូបភាពវិក្កយបត្រ / Invoice Image',
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

            // Item List
            const Text(
              'បញ្ជីឧបករណ៍ / Items List',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            if (details.isEmpty)
              const Center(child: Text('គ្មានទំនិញទេ / No items found.'))
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: details.length,
                itemBuilder: (context, index) {
                  final detail = details[index];
                  final product = detail['product'];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    child: ListTile(
                      title: Text(
                        product?['name'] ?? 'Unknown Model',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text(detail['note'] != null && detail['note'].toString().trim().isNotEmpty
                          ? 'Note: ${detail['note']}'
                          : 'No item note'),
                      trailing: CircleAvatar(
                        backgroundColor: Colors.green.shade50,
                        child: Text(
                          '${detail['quantity']}',
                          style: TextStyle(color: Colors.green.shade800, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                  );
                },
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
