import 'package:flutter/material.dart';
import '../../../core/constants/constants.dart';

class ModelDetailScreen extends StatelessWidget {
  final Map<String, dynamic> model;

  const ModelDetailScreen({super.key, required this.model});

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue.shade800;
    final imageUrl = AppConstants.formatImageUrl(model['image']);

    return Scaffold(
      appBar: AppBar(
        title: Text(model['model_name']),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Model Image
            Hero(
              tag: 'model_image_${model['id']}',
              child: Container(
                width: double.infinity,
                height: 250,
                color: Colors.grey.shade100,
                child: imageUrl != null
                    ? Image.network(
                        imageUrl,
                        fit: BoxFit.contain,
                        errorBuilder: (_, __, ___) => _buildPlaceholderIcon(),
                      )
                    : _buildPlaceholderIcon(),
              ),
            ),
            
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Title & Brand
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          model['model_name'],
                          style: const TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      Chip(
                        label: Text(model['brand_name'] ?? 'Unknown'),
                        backgroundColor: Colors.blue.shade50,
                        labelStyle: TextStyle(color: primaryColor),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Stock Summary Grid
                  const Text(
                    'ស្ថានភាពស្តុក / Stock Status',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 12),
                  GridView.count(
                    crossAxisCount: 2,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                    childAspectRatio: 2.2,
                    children: [
                      _buildStatCard('នាំចូលសរុប\nTotal In', '${model['stock_in']}', Colors.green),
                      _buildStatCard('នាំចេញសរុប\nTotal Out', '${model['stock_out']}', Colors.orange),
                      _buildStatCard('កំពុងខ្ចី\nBorrowed', '${model['borrow']}', Colors.red),
                      _buildStatCard('អាចប្រើបាន\nAvailable', '${model['available_stock']}', Colors.blue),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Technical Specifications
                  const Text(
                    'លក្ខណៈបច្ចេកទេស / Specifications',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 12),
                  Card(
                    elevation: 1,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        children: [
                          _buildSpecRow('ប្រភេទឧបករណ៍ / Type', model['type'] ?? 'N/A'),
                          const Divider(),
                          _buildSpecRow('Accessory', model['accessory'] == true ? 'Yes' : 'No'),
                          const Divider(),
                          _buildSpecRow('ប្រេកង់ / Frequency', model['frequency'] ?? 'N/A'),
                          const Divider(),
                          _buildSpecRow('កម្លាំង / Power', model['power'] ?? 'N/A'),
                          const Divider(),
                          _buildSpecRow('ទំហំផ្ទុក / Capacity', model['capacity'] ?? 'N/A'),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Description
                  const Text(
                    'ការពិពណ៌នា / Description',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 12),
                  Card(
                    elevation: 1,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Text(
                        model['description'] != null && model['description'].toString().trim().isNotEmpty
                            ? model['description']
                            : 'មិនមានការពិពណ៌នាទេ / No description available.',
                        style: const TextStyle(fontSize: 14, height: 1.4),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPlaceholderIcon() {
    return Center(
      child: Icon(
        Icons.radio,
        size: 100,
        color: Colors.blue.shade100,
      ),
    );
  }

  Widget _buildStatCard(String title, String value, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              title,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w500,
                color: Colors.grey.shade800,
              ),
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: color.withOpacity(0.9),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSpecRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(color: Colors.grey.shade600, fontSize: 14),
          ),
          Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
          ),
        ],
      ),
    );
  }
}
