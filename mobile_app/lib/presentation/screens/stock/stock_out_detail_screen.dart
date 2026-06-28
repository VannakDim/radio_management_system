import 'package:flutter/material.dart';
import '../../../core/constants/constants.dart';

class StockOutDetailScreen extends StatelessWidget {
  final Map<String, dynamic> item;

  const StockOutDetailScreen({super.key, required this.item});

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;
    final imageUrl = AppConstants.formatImageUrl(item['image']);
    
    // Radios (serialized products)
    final products = item['products'] as List<dynamic>? ?? [];
    // Accessories (simple quantities)
    final accessories = item['stock_out_details'] as List<dynamic>? ?? [];

    return Scaffold(
      appBar: AppBar(
        title: Text('Stock Out Details'),
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
                    _buildInfoRow('Receiver / អ្នកទទួល:', item['receiver'] ?? 'Unknown'),
                    _buildInfoRow('Type / ប្រភេទ:', item['type'] ?? 'N/A'),
                    _buildInfoRow('Date / កាលបរិច្ឆេទ:', item['created_at'] != null ? item['created_at'].substring(0, 10) : 'N/A'),
                    _buildInfoRow('Recorded by / ផ្ទេរដោយ:', item['user']?['name'] ?? 'Unknown'),
                    _buildInfoRow('Note / កំណត់សម្គាល់:', item['note'] ?? 'No notes.'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Signature / Photo if any
            if (imageUrl != null) ...[
              const Text(
                'រូបភាពហត្ថលេខា/ភស្តុតាង / Signature or Photo',
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

            // Radios Section
            if (products.isNotEmpty) ...[
              const Text(
                'វិទ្យុទាក់ទង / Radios List',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.blue),
              ),
              const SizedBox(height: 8),
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: products.length,
                itemBuilder: (context, index) {
                  final outProduct = products[index];
                  final product = outProduct['product'];
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
                      subtitle: Text('S/N: ${product?['PID'] ?? "Unknown"}'),
                      trailing: Text(
                        '1 unit',
                        style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                      ),
                    ),
                  );
                },
              ),
              const SizedBox(height: 16),
            ],

            // Accessories Section
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
                  final detail = accessories[index];
                  final productModel = detail['product']; // Pointing to ProductModel
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    child: ListTile(
                      leading: const Icon(Icons.electrical_services, color: Colors.orange),
                      title: Text(
                        productModel?['name'] ?? 'Unknown Model',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text(detail['note'] != null && detail['note'].toString().trim().isNotEmpty
                          ? 'Note: ${detail['note']}'
                          : 'No accessory note'),
                      trailing: CircleAvatar(
                        backgroundColor: Colors.orange.shade50,
                        radius: 18,
                        child: Text(
                          '${detail['quantity']}',
                          style: TextStyle(color: Colors.orange.shade800, fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ],
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
