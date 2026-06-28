import 'dart:async';
import 'dart:io';
import 'dart:typed_data';
import 'dart:ui' as ui;
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:image/image.dart' as img_lib;
import 'package:path_provider/path_provider.dart';

class ImageEditorScreen extends StatefulWidget {
  final XFile imageFile;

  const ImageEditorScreen({super.key, required this.imageFile});

  @override
  State<ImageEditorScreen> createState() => _ImageEditorScreenState();
}

class _ImageEditorScreenState extends State<ImageEditorScreen> {
  Uint8List? _imageBytes;
  bool _loading = true;
  int _rotationAngle = 0; // 0, 90, 180, 270
  String _compressionLevel = 'Medium'; // Low, Medium, High
  double? _lockedAspectRatio; // null = Free, 1/1.414 = A4, 4/3 = Card
  int? _imageWidth;
  int? _imageHeight;

  // 4 corners of the perspective crop (normalized 0.0 to 1.0)
  Offset _topLeft = const Offset(0.15, 0.15);
  Offset _topRight = const Offset(0.85, 0.15);
  Offset _bottomLeft = const Offset(0.15, 0.85);
  Offset _bottomRight = const Offset(0.85, 0.85);

  @override
  void initState() {
    super.initState();
    _loadBytes();
  }

  Future<void> _loadBytes() async {
    try {
      final bytes = await widget.imageFile.readAsBytes();
      
      final Completer<ui.Image> completer = Completer();
      ui.decodeImageFromList(bytes, (ui.Image img) {
        completer.complete(img);
      });
      final ui.Image img = await completer.future;

      setState(() {
        _imageBytes = bytes;
        _imageWidth = img.width;
        _imageHeight = img.height;
        _loading = false;
      });

      img.dispose();
    } catch (_) {
      setState(() {
        _loading = false;
      });
    }
  }

  void _rotate() {
    setState(() {
      _rotationAngle = (_rotationAngle + 90) % 360;

      // After rotating, reset the crop points to a centered document shape
      _topLeft = const Offset(0.15, 0.15);
      _topRight = const Offset(0.85, 0.15);
      _bottomLeft = const Offset(0.15, 0.85);
      _bottomRight = const Offset(0.85, 0.85);
    });
  }

  void _setSuggestRatio(double ratio) {
    if (_imageWidth == null || _imageHeight == null) return;
    
    final bool isRotated = _rotationAngle == 90 || _rotationAngle == 270;
    final double srcW = (isRotated ? _imageHeight! : _imageWidth!).toDouble();
    final double srcH = (isRotated ? _imageWidth! : _imageHeight!).toDouble();
    final double imageRatio = srcW / srcH;

    setState(() {
      double targetWidth = 0.7;
      double targetHeight = targetWidth * imageRatio / ratio;
      if (targetHeight > 0.9) {
        final double scale = 0.9 / targetHeight;
        targetWidth *= scale;
        targetHeight = 0.9;
      }
      if (targetWidth > 0.9) {
        final double scale = 0.9 / targetWidth;
        targetHeight *= scale;
        targetWidth = 0.9;
      }

      final double l = 0.5 - (targetWidth / 2);
      final double r = 0.5 + (targetWidth / 2);
      final double t = 0.5 - (targetHeight / 2);
      final double b = 0.5 + (targetHeight / 2);

      _topLeft = Offset(l.clamp(0.02, 0.98), t.clamp(0.02, 0.98));
      _topRight = Offset(r.clamp(0.02, 0.98), t.clamp(0.02, 0.98));
      _bottomLeft = Offset(l.clamp(0.02, 0.98), b.clamp(0.02, 0.98));
      _bottomRight = Offset(r.clamp(0.02, 0.98), b.clamp(0.02, 0.98));
    });
  }

  void _onHandleDrag(String label, Offset newOffset) {
    if (_lockedAspectRatio != null) {
      _resizeLockedCropRegion(label, newOffset);
    } else {
      setState(() {
        if (label == 'TL') {
          _topLeft = Offset(
            newOffset.dx.clamp(0.0, _topRight.dx - 0.08),
            newOffset.dy.clamp(0.0, _bottomLeft.dy - 0.08),
          );
        } else if (label == 'TR') {
          _topRight = Offset(
            newOffset.dx.clamp(_topLeft.dx + 0.08, 1.0),
            newOffset.dy.clamp(0.0, _bottomRight.dy - 0.08),
          );
        } else if (label == 'BL') {
          _bottomLeft = Offset(
            newOffset.dx.clamp(0.0, _bottomRight.dx - 0.08),
            newOffset.dy.clamp(_topLeft.dy + 0.08, 1.0),
          );
        } else if (label == 'BR') {
          _bottomRight = Offset(
            newOffset.dx.clamp(_bottomLeft.dx + 0.08, 1.0),
            newOffset.dy.clamp(_topRight.dy + 0.08, 1.0),
          );
        }
      });
    }
  }

  void _resizeLockedCropRegion(String corner, Offset newPos) {
    if (_imageWidth == null || _imageHeight == null) return;
    final double ratio = _lockedAspectRatio!;
    
    final bool isRotated = _rotationAngle == 90 || _rotationAngle == 270;
    final double srcW = (isRotated ? _imageHeight! : _imageWidth!).toDouble();
    final double srcH = (isRotated ? _imageWidth! : _imageHeight!).toDouble();
    final double imageRatio = srcW / srcH;

    setState(() {
      if (corner == 'TL') {
        // Opposite is BR
        final double w = (_bottomRight.dx - newPos.dx).clamp(0.08, 1.0);
        final double h = (w * imageRatio / ratio).clamp(0.08, 1.0);
        final double newX = _bottomRight.dx - w;
        final double newY = _bottomRight.dy - h;
        if (newX >= 0.0 && newY >= 0.0) {
          _topLeft = Offset(newX, newY);
          _topRight = Offset(_bottomRight.dx, newY);
          _bottomLeft = Offset(newX, _bottomRight.dy);
        }
      } else if (corner == 'TR') {
        // Opposite is BL
        final double w = (newPos.dx - _bottomLeft.dx).clamp(0.08, 1.0);
        final double h = (w * imageRatio / ratio).clamp(0.08, 1.0);
        final double newX = _bottomLeft.dx + w;
        final double newY = _bottomLeft.dy - h;
        if (newX <= 1.0 && newY >= 0.0) {
          _topRight = Offset(newX, newY);
          _topLeft = Offset(_bottomLeft.dx, newY);
          _bottomRight = Offset(newX, _bottomLeft.dy);
        }
      } else if (corner == 'BL') {
        // Opposite is TR
        final double w = (_topRight.dx - newPos.dx).clamp(0.08, 1.0);
        final double h = (w * imageRatio / ratio).clamp(0.08, 1.0);
        final double newX = _topRight.dx - w;
        final double newY = _topRight.dy + h;
        if (newX >= 0.0 && newY <= 1.0) {
          _bottomLeft = Offset(newX, newY);
          _topLeft = Offset(newX, _topRight.dy);
          _bottomRight = Offset(_topRight.dx, newY);
        }
      } else if (corner == 'BR') {
        // Opposite is TL
        final double w = (newPos.dx - _topLeft.dx).clamp(0.08, 1.0);
        final double h = (w * imageRatio / ratio).clamp(0.08, 1.0);
        final double newX = _topLeft.dx + w;
        final double newY = _topLeft.dy + h;
        if (newX <= 1.0 && newY <= 1.0) {
          _bottomRight = Offset(newX, newY);
          _topRight = Offset(newX, _topLeft.dy);
          _bottomLeft = Offset(_topLeft.dx, newY);
        }
      }
    });
  }

  void _panCropRegion(double dx, double dy) {
    setState(() {
      final double newTLx = _topLeft.dx + dx;
      final double newTLy = _topLeft.dy + dy;
      final double newTRx = _topRight.dx + dx;
      final double newTRy = _topRight.dy + dy;
      final double newBLx = _bottomLeft.dx + dx;
      final double newBLy = _bottomLeft.dy + dy;
      final double newBRx = _bottomRight.dx + dx;
      final double newBRy = _bottomRight.dy + dy;

      if (newTLx >= 0.0 && newTLx <= 1.0 && newTLy >= 0.0 && newTLy <= 1.0 &&
          newTRx >= 0.0 && newTRx <= 1.0 && newTRy >= 0.0 && newTRy <= 1.0 &&
          newBLx >= 0.0 && newBLx <= 1.0 && newBLy >= 0.0 && newBLy <= 1.0 &&
          newBRx >= 0.0 && newBRx <= 1.0 && newBRy >= 0.0 && newBRy <= 1.0) {
        _topLeft = Offset(newTLx, newTLy);
        _topRight = Offset(newTRx, newTRy);
        _bottomLeft = Offset(newBLx, newBLy);
        _bottomRight = Offset(newBRx, newBRy);
      }
    });
  }

  Future<void> _autoDetectCorners() async {
    if (_imageBytes == null) return;
    setState(() {
      _loading = true;
    });

    try {
      final List<Offset> corners = await compute(_autoDetectCornersIsolate, _imageBytes!);
      setState(() {
        _topLeft = corners[0];
        _topRight = corners[1];
        _bottomLeft = corners[2];
        _bottomRight = corners[3];
      });
    } catch (e) {
      debugPrint('Error auto-detecting corners: $e');
    } finally {
      setState(() {
        _loading = false;
      });
    }
  }

  Future<void> _saveAndReturn() async {
    if (_imageBytes == null) return;

    setState(() {
      _loading = true;
    });

    try {
      // Get base output width based on quality setting
      int baseWidth = 800;
      if (_compressionLevel == 'High') {
        baseWidth = 1200;
      } else if (_compressionLevel == 'Low') {
        baseWidth = 500;
      }

      // Get source dimensions
      final bool isRotated = _rotationAngle == 90 || _rotationAngle == 270;
      final double srcW = (isRotated ? _imageHeight! : _imageWidth!).toDouble();
      final double srcH = (isRotated ? _imageWidth! : _imageHeight!).toDouble();

      // Convert normalized crop coordinates into physical pixel coordinates
      final Offset p0 = Offset(_topLeft.dx * srcW, _topLeft.dy * srcH);
      final Offset p1 = Offset(_topRight.dx * srcW, _topRight.dy * srcH);
      final Offset p2 = Offset(_bottomLeft.dx * srcW, _bottomLeft.dy * srcH);
      final Offset p3 = Offset(_bottomRight.dx * srcW, _bottomRight.dy * srcH);

      // Calculate physical distances of crop edges
      final double topDist = (p1 - p0).distance;
      final double bottomDist = (p3 - p2).distance;
      final double avgWidth = (topDist + bottomDist) / 2;

      final double leftDist = (p2 - p0).distance;
      final double rightDist = (p3 - p1).distance;
      final double avgHeight = (leftDist + rightDist) / 2;

      final double quadRatio = avgWidth / avgHeight;

      // Determine target aspect ratio to use
      double targetRatio;
      if (_lockedAspectRatio != null) {
        // Use locked ratio, checking if orientation is landscape or portrait
        if (quadRatio > 1.0 && _lockedAspectRatio! < 1.0) {
          targetRatio = 1.0 / _lockedAspectRatio!;
        } else if (quadRatio < 1.0 && _lockedAspectRatio! > 1.0) {
          targetRatio = 1.0 / _lockedAspectRatio!;
        } else {
          targetRatio = _lockedAspectRatio!;
        }
      } else {
        // Free ratio: use natural shape of crop
        targetRatio = quadRatio;
      }

      int destWidth = baseWidth;
      int destHeight = (destWidth / targetRatio).round();

      final croppedBytes = await performPerspectiveWarp(
        imageBytes: _imageBytes!,
        topLeft: _topLeft,
        topRight: _topRight,
        bottomLeft: _bottomLeft,
        bottomRight: _bottomRight,
        rotationAngle: _rotationAngle,
        destWidth: destWidth,
        destHeight: destHeight,
      );

      final tempDir = await getTemporaryDirectory();
      final tempFile = File('${tempDir.path}/scanned_${DateTime.now().millisecondsSinceEpoch}.jpg');
      await tempFile.writeAsBytes(croppedBytes);
      
      final editedFile = XFile(tempFile.path);

      if (mounted) {
        Navigator.pop(context, editedFile);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error warping image: $e')),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.orange.shade700;

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: const Text('តម្រង់រូបភាព / Document Scan & Warp', style: TextStyle(color: Colors.white, fontSize: 18)),
        backgroundColor: Colors.black,
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          if (!_loading && _imageBytes != null)
            TextButton.icon(
              onPressed: _saveAndReturn,
              icon: const Icon(Icons.check, color: Colors.greenAccent),
              label: const Text(
                'រួចរាល់ / Done',
                style: TextStyle(color: Colors.greenAccent, fontWeight: FontWeight.bold, fontSize: 16),
              ),
            ),
        ],
      ),
      body: _loading
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const CircularProgressIndicator(color: Colors.orange),
                  const SizedBox(height: 16),
                  Text(
                    'កំពុងតម្រង់រូបភាពចតុកោណ... / Processing Warp...',
                    style: TextStyle(color: Colors.grey.shade400),
                  ),
                ],
              ),
            )
          : _imageBytes == null
              ? const Center(child: Text('Unable to load image', style: TextStyle(color: Colors.white)))
              : Column(
                  children: [
                    Expanded(
                      child: LayoutBuilder(
                        builder: (context, constraints) {
                          if (_imageWidth == null || _imageHeight == null) {
                            return const Center(
                              child: CircularProgressIndicator(color: Colors.orange),
                            );
                          }

                          final double parentW = constraints.maxWidth;
                          final double parentH = constraints.maxHeight;

                          final bool isRotated = _rotationAngle == 90 || _rotationAngle == 270;
                          final double srcW = isRotated ? _imageHeight!.toDouble() : _imageWidth!.toDouble();
                          final double srcH = isRotated ? _imageWidth!.toDouble() : _imageHeight!.toDouble();

                          final double imageRatio = srcW / srcH;
                          final double parentRatio = parentW / parentH;

                          double renderedW, renderedH;
                          double offsetX, offsetY;

                          if (imageRatio > parentRatio) {
                            renderedW = parentW;
                            renderedH = parentW / imageRatio;
                            offsetX = 0.0;
                            offsetY = (parentH - renderedH) / 2;
                          } else {
                            renderedH = parentH;
                            renderedW = parentH * imageRatio;
                            offsetX = (parentW - renderedW) / 2;
                            offsetY = 0.0;
                          }

                          return Stack(
                            children: [
                              // 1. Raw Rotated Image positioned precisely
                              Positioned(
                                left: offsetX,
                                top: offsetY,
                                width: renderedW,
                                height: renderedH,
                                child: RotatedBox(
                                  quarterTurns: _rotationAngle ~/ 90,
                                  child: Image.memory(
                                    _imageBytes!,
                                    fit: BoxFit.fill,
                                  ),
                                ),
                              ),
                              // Background GestureDetector for panning the entire crop region when locked
                              if (_lockedAspectRatio != null)
                                Positioned(
                                  left: offsetX,
                                  top: offsetY,
                                  width: renderedW,
                                  height: renderedH,
                                  child: GestureDetector(
                                    onPanUpdate: (details) {
                                      final double dx = details.delta.dx / renderedW;
                                      final double dy = details.delta.dy / renderedH;
                                      _panCropRegion(dx, dy);
                                    },
                                    child: Container(
                                      color: Colors.transparent,
                                    ),
                                  ),
                                ),
                              // 2. Custom Painter for crop overlay positioned precisely over the image
                              Positioned(
                                left: offsetX,
                                top: offsetY,
                                width: renderedW,
                                height: renderedH,
                                child: CustomPaint(
                                  painter: PerspectiveOverlayPainter(
                                    topLeft: _topLeft,
                                    topRight: _topRight,
                                    bottomLeft: _bottomLeft,
                                    bottomRight: _bottomRight,
                                  ),
                                ),
                              ),
                              // 3. Draggable Handles at 4 corners
                              _buildHandle(
                                currentOffset: _topLeft,
                                label: 'TL',
                                renderedW: renderedW,
                                renderedH: renderedH,
                                offsetX: offsetX,
                                offsetY: offsetY,
                                onDrag: (newOffset) => _onHandleDrag('TL', newOffset),
                              ),
                              _buildHandle(
                                currentOffset: _topRight,
                                label: 'TR',
                                renderedW: renderedW,
                                renderedH: renderedH,
                                offsetX: offsetX,
                                offsetY: offsetY,
                                onDrag: (newOffset) => _onHandleDrag('TR', newOffset),
                              ),
                              _buildHandle(
                                currentOffset: _bottomLeft,
                                label: 'BL',
                                renderedW: renderedW,
                                renderedH: renderedH,
                                offsetX: offsetX,
                                offsetY: offsetY,
                                onDrag: (newOffset) => _onHandleDrag('BL', newOffset),
                              ),
                              _buildHandle(
                                currentOffset: _bottomRight,
                                label: 'BR',
                                renderedW: renderedW,
                                renderedH: renderedH,
                                offsetX: offsetX,
                                offsetY: offsetY,
                                onDrag: (newOffset) => _onHandleDrag('BR', newOffset),
                              ),
                            ],
                          );
                        },
                      ),
                    ),
                    _buildToolsPanel(primaryColor),
                  ],
                ),
    );
  }

  Widget _buildHandle({
    required Offset currentOffset,
    required String label,
    required double renderedW,
    required double renderedH,
    required double offsetX,
    required double offsetY,
    required Function(Offset) onDrag,
  }) {
    final double left = offsetX + currentOffset.dx * renderedW - 20;
    final double top = offsetY + currentOffset.dy * renderedH - 20;

    return Positioned(
      left: left,
      top: top,
      child: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onPanUpdate: (details) {
          final double dx = details.delta.dx / renderedW;
          final double dy = details.delta.dy / renderedH;
          final double newX = (currentOffset.dx + dx).clamp(0.0, 1.0);
          final double newY = (currentOffset.dy + dy).clamp(0.0, 1.0);
          onDrag(Offset(newX, newY));
        },
        child: Container(
          width: 40,
          height: 40,
          alignment: Alignment.center,
          child: Container(
            width: 24,
            height: 24,
            decoration: BoxDecoration(
              color: Colors.orange.shade700,
              shape: BoxShape.circle,
              border: Border.all(color: Colors.white, width: 2),
              boxShadow: const [
                BoxShadow(color: Colors.black54, blurRadius: 4),
              ],
            ),
            child: const Center(
              child: Icon(Icons.zoom_out_map, size: 10, color: Colors.white),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildToolsPanel(Color primaryColor) {
    return Container(
      color: Colors.grey.shade900,
      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              _buildToolButton(
                icon: Icons.rotate_right,
                label: 'បង្វិល / Rotate',
                isActive: false,
                onTap: _rotate,
              ),
              _buildToolButton(
                icon: Icons.crop_free,
                label: 'សេរី / Free Crop',
                isActive: _lockedAspectRatio == null,
                onTap: () {
                  setState(() {
                    _lockedAspectRatio = null;
                  });
                  _autoDetectCorners();
                },
              ),
              _buildToolButton(
                icon: Icons.picture_as_pdf,
                label: 'ឯកសារ A4 / A4 Doc',
                isActive: _lockedAspectRatio != null && (_lockedAspectRatio! - (1.0 / 1.414)).abs() < 0.01,
                onTap: () {
                  setState(() {
                    _lockedAspectRatio = 1.0 / 1.414;
                  });
                  _setSuggestRatio(1.0 / 1.414);
                },
              ),
              _buildToolButton(
                icon: Icons.credit_card,
                label: 'កាត 4:3 / Card 4:3',
                isActive: _lockedAspectRatio != null && (_lockedAspectRatio! - (4.0 / 3.0)).abs() < 0.01,
                onTap: () {
                  setState(() {
                    _lockedAspectRatio = 4.0 / 3.0;
                  });
                  _setSuggestRatio(4.0 / 3.0);
                },
              ),
            ],
          ),
          if (_lockedAspectRatio == null) ...[
            const SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: _autoDetectCorners,
              icon: const Icon(Icons.auto_awesome),
              label: const Text('សម្គាល់ស្វ័យប្រវត្ត / Auto Detect Corners'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.orange.shade700,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
            ),
          ],
          const SizedBox(height: 16),
          const Divider(color: Colors.grey, height: 1),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'ទំហំរូបភាព / Image Quality:',
                style: TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                decoration: BoxDecoration(
                  color: Colors.black,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.grey.shade700),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    dropdownColor: Colors.black,
                    value: _compressionLevel,
                    items: const [
                      DropdownMenuItem(
                        value: 'Low',
                        child: Text('តូច (Low Size - 500px)', style: TextStyle(color: Colors.white, fontSize: 12)),
                      ),
                      DropdownMenuItem(
                        value: 'Medium',
                        child: Text('មធ្យម (Medium - 800px)', style: TextStyle(color: Colors.white, fontSize: 12)),
                      ),
                      DropdownMenuItem(
                        value: 'High',
                        child: Text('ច្បាស់ (High - 1200px)', style: TextStyle(color: Colors.white, fontSize: 12)),
                      ),
                    ],
                    onChanged: (val) {
                      if (val != null) {
                        setState(() {
                          _compressionLevel = val;
                        });
                      }
                    },
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildToolButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
    bool isActive = false,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 8),
        decoration: BoxDecoration(
          color: isActive ? Colors.orange.shade700.withOpacity(0.15) : Colors.transparent,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isActive ? Colors.orange.shade700 : Colors.transparent,
            width: 1,
          ),
        ),
        child: Column(
          children: [
            Icon(
              icon,
              color: isActive ? Colors.orange.shade400 : Colors.orange.shade600,
              size: 26,
            ),
            const SizedBox(height: 4),
            Text(
              label.split(' / ')[0],
              style: TextStyle(
                color: isActive ? Colors.orange.shade400 : Colors.white,
                fontSize: 10,
                fontWeight: FontWeight.w500,
              ),
            ),
            Text(
              label.split(' / ')[1],
              style: TextStyle(
                color: isActive ? Colors.orange.shade400.withOpacity(0.8) : Colors.grey.shade500,
                fontSize: 8,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class PerspectiveOverlayPainter extends CustomPainter {
  final Offset topLeft;
  final Offset topRight;
  final Offset bottomLeft;
  final Offset bottomRight;

  PerspectiveOverlayPainter({
    required this.topLeft,
    required this.topRight,
    required this.bottomLeft,
    required this.bottomRight,
  });

  @override
  void paint(ui.Canvas canvas, ui.Size size) {
    final paint = ui.Paint()..color = Colors.black.withOpacity(0.65);

    // Absolute pixel locations
    final p0 = Offset(topLeft.dx * size.width, topLeft.dy * size.height);
    final p1 = Offset(topRight.dx * size.width, topRight.dy * size.height);
    final p2 = Offset(bottomLeft.dx * size.width, bottomLeft.dy * size.height);
    final p3 = Offset(bottomRight.dx * size.width, bottomRight.dy * size.height);

    // Create Path for the crop quadrilateral
    final quadPath = ui.Path()
      ..moveTo(p0.dx, p0.dy)
      ..lineTo(p1.dx, p1.dy)
      ..lineTo(p3.dx, p3.dy) // BR
      ..lineTo(p2.dx, p2.dy) // BL
      ..close();

    // Mask the area outside the quadrilateral
    final outerPath = ui.Path()
      ..addRect(ui.Rect.fromLTWH(0, 0, size.width, size.height));
    final maskPath = ui.Path.combine(ui.PathOperation.difference, outerPath, quadPath);

    canvas.drawPath(maskPath, paint);

    // Draw borders
    final borderPaint = ui.Paint()
      ..color = Colors.orange.shade700
      ..strokeWidth = 2.5
      ..style = ui.PaintingStyle.stroke;
    canvas.drawPath(quadPath, borderPaint);

    // Draw thin diagonal guidelines inside the quadrilateral (scanner aid)
    final gridPaint = ui.Paint()
      ..color = Colors.orange.withOpacity(0.3)
      ..strokeWidth = 1.0
      ..style = ui.PaintingStyle.stroke;
    canvas.drawLine(p0, p3, gridPaint);
    canvas.drawLine(p1, p2, gridPaint);
  }

  @override
  bool shouldRepaint(covariant PerspectiveOverlayPainter oldDelegate) {
    return oldDelegate.topLeft != topLeft ||
        oldDelegate.topRight != topRight ||
        oldDelegate.bottomLeft != bottomLeft ||
        oldDelegate.bottomRight != bottomRight;
  }
}

// Solves A * h = B system of 8 linear equations for homography
List<double> solveHomographyMatrix(
  double x0, double y0,
  double x1, double y1,
  double x2, double y2,
  double x3, double y3,
  double w, double h,
) {
  final List<List<double>> A = List.generate(8, (_) => List.filled(9, 0.0));

  void fillRow(int r, double u, double v, double tx, double ty) {
    A[r * 2][0] = u;
    A[r * 2][1] = v;
    A[r * 2][2] = 1.0;
    A[r * 2][6] = -u * tx;
    A[r * 2][7] = -v * tx;
    A[r * 2][8] = tx;

    A[r * 2 + 1][3] = u;
    A[r * 2 + 1][4] = v;
    A[r * 2 + 1][5] = 1.0;
    A[r * 2 + 1][6] = -u * ty;
    A[r * 2 + 1][7] = -v * ty;
    A[r * 2 + 1][8] = ty;
  }

  // Correspondences:
  // Output point (0, 0) maps to source TL (x0, y0)
  // Output point (w, 0) maps to source TR (x1, y1)
  // Output point (w, h) maps to source BR (x3, y3)
  // Output point (0, h) maps to source BL (x2, y2)
  fillRow(0, 0.0, 0.0, x0, y0);
  fillRow(1, w, 0.0, x1, y1);
  fillRow(2, w, h, x3, y3);
  fillRow(3, 0.0, h, x2, y2);

  // Gauss-Jordan elimination
  for (int i = 0; i < 8; i++) {
    int pivot = i;
    for (int j = i + 1; j < 8; j++) {
      if (A[j][i].abs() > A[pivot][i].abs()) {
        pivot = j;
      }
    }
    final temp = A[i];
    A[i] = A[pivot];
    A[pivot] = temp;

    for (int j = i + 1; j < 8; j++) {
      final factor = A[j][i] / A[i][i];
      for (int k = i; k < 9; k++) {
        A[j][k] -= factor * A[i][k];
      }
    }
  }

  final List<double> coeff = List.filled(8, 0.0);
  for (int i = 7; i >= 0; i--) {
    double sum = A[i][8];
    for (int j = i + 1; j < 8; j++) {
      sum -= A[i][j] * coeff[j];
    }
    coeff[i] = sum / A[i][i];
  }

  return coeff;
}

Future<Uint8List> performPerspectiveWarp({
  required Uint8List imageBytes,
  required Offset topLeft,
  required Offset topRight,
  required Offset bottomLeft,
  required Offset bottomRight,
  required int rotationAngle,
  required int destWidth,
  required int destHeight,
}) async {
  return compute(_warpImageIsolate, {
    'imageBytes': imageBytes,
    'topLeft': topLeft,
    'topRight': topRight,
    'bottomLeft': bottomLeft,
    'bottomRight': bottomRight,
    'rotationAngle': rotationAngle,
    'destWidth': destWidth,
    'destHeight': destHeight,
  });
}

// Background Isolate Functions using pure-Dart package:image

List<Offset> _autoDetectCornersIsolate(Uint8List bytes) {
  final img_lib.Image? img = img_lib.decodeImage(bytes);
  if (img == null) {
    throw Exception('Failed to decode image');
  }

  final int w = img.width;
  final int h = img.height;

  const int gridW = 100;
  const int gridH = 100;
  final List<double> luminanceGrid = List.filled(gridW * gridH, 0.0);

  double totalLuminance = 0.0;
  for (int gy = 0; gy < gridH; gy++) {
    final int py = ((gy / gridH) * h).round().clamp(0, h - 1);
    for (int gx = 0; gx < gridW; gx++) {
      final int px = ((gx / gridW) * w).round().clamp(0, w - 1);
      
      final img_lib.Pixel pixel = img.getPixel(px, py);
      final double r = pixel.r.toDouble();
      final double g = pixel.g.toDouble();
      final double b = pixel.b.toDouble();
      final double lum = 0.299 * r + 0.587 * g + 0.114 * b;
      luminanceGrid[gy * gridW + gx] = lum;
      totalLuminance += lum;
    }
  }

  final double avgLuminance = totalLuminance / (gridW * gridH);

  double cornerLuminanceSum = 0.0;
  int cornerCount = 0;
  for (int y in [0, 1, 2, gridH - 3, gridH - 2, gridH - 1]) {
    for (int x in [0, 1, 2, gridW - 3, gridW - 2, gridW - 1]) {
      cornerLuminanceSum += luminanceGrid[y * gridW + x];
      cornerCount++;
    }
  }
  final double backgroundAvg = cornerLuminanceSum / cornerCount;
  final bool docIsLighter = backgroundAvg < avgLuminance;

  final List<bool> isForeground = List.generate(gridW * gridH, (i) {
    final double lum = luminanceGrid[i];
    return docIsLighter ? (lum > avgLuminance * 1.05) : (lum < avgLuminance * 0.95);
  });

  int minSum = 10000;
  int maxSum = -10000;
  int maxDiff = -10000;
  int minDiff = 10000;

  Offset tl = const Offset(0.15, 0.15);
  Offset tr = const Offset(0.85, 0.15);
  Offset bl = const Offset(0.15, 0.85);
  Offset br = const Offset(0.85, 0.85);

  bool foundAny = false;

  for (int gy = 0; gy < gridH; gy++) {
    for (int gx = 0; gx < gridW; gx++) {
      if (isForeground[gy * gridW + gx]) {
        foundAny = true;
        final int sum = gx + gy;
        final int diff = gx - gy;

        if (sum < minSum) {
          minSum = sum;
          tl = Offset(gx / gridW, gy / gridH);
        }
        if (sum > maxSum) {
          maxSum = sum;
          br = Offset(gx / gridW, gy / gridH);
        }
        if (diff > maxDiff) {
          maxDiff = diff;
          tr = Offset(gx / gridW, gy / gridH);
        }
        if (diff < minDiff) {
          minDiff = diff;
          bl = Offset(gx / gridW, gy / gridH);
        }
      }
    }
  }

  return [
    Offset(tl.dx.clamp(0.02, 0.98), tl.dy.clamp(0.02, 0.98)),
    Offset(tr.dx.clamp(0.02, 0.98), tr.dy.clamp(0.02, 0.98)),
    Offset(bl.dx.clamp(0.02, 0.98), bl.dy.clamp(0.02, 0.98)),
    Offset(br.dx.clamp(0.02, 0.98), br.dy.clamp(0.02, 0.98)),
  ];
}

Uint8List _warpImageIsolate(Map<String, dynamic> params) {
  final Uint8List imageBytes = params['imageBytes'];
  final Offset topLeft = params['topLeft'];
  final Offset topRight = params['topRight'];
  final Offset bottomLeft = params['bottomLeft'];
  final Offset bottomRight = params['bottomRight'];
  final int rotationAngle = params['rotationAngle'];
  final int destWidth = params['destWidth'];
  final int destHeight = params['destHeight'];

  final img_lib.Image? originalImage = img_lib.decodeImage(imageBytes);
  if (originalImage == null) {
    throw Exception('Failed to decode image');
  }

  img_lib.Image sourceImage = originalImage;
  if (rotationAngle != 0) {
    sourceImage = img_lib.copyRotate(originalImage, angle: rotationAngle);
  }

  final int srcW = sourceImage.width;
  final int srcH = sourceImage.height;

  final double x0 = topLeft.dx * srcW;
  final double y0 = topLeft.dy * srcH;
  final double x1 = topRight.dx * srcW;
  final double y1 = topRight.dy * srcH;
  final double x2 = bottomLeft.dx * srcW;
  final double y2 = bottomLeft.dy * srcH;
  final double x3 = bottomRight.dx * srcW;
  final double y3 = bottomRight.dy * srcH;

  final List<double> h = solveHomographyMatrix(
    x0, y0,
    x1, y1,
    x2, y2,
    x3, y3,
    destWidth.toDouble(),
    destHeight.toDouble(),
  );

  final img_lib.Image destImage = img_lib.Image(width: destWidth, height: destHeight);

  for (int v = 0; v < destHeight; v++) {
    for (int u = 0; u < destWidth; u++) {
      final double divisor = h[6] * u + h[7] * v + 1.0;
      if (divisor.abs() < 0.0001) continue;

      final double sx = (h[0] * u + h[1] * v + h[2]) / divisor;
      final double sy = (h[3] * u + h[4] * v + h[5]) / divisor;

      final int px = sx.round().clamp(0, srcW - 1);
      final int py = sy.round().clamp(0, srcH - 1);

      final img_lib.Pixel pixel = sourceImage.getPixel(px, py);
      destImage.setPixel(u, v, pixel);
    }
  }

  return Uint8List.fromList(img_lib.encodeJpg(destImage, quality: 85));
}
