import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../bloc/auth_bloc.dart';
import '../bloc/auth_event.dart';
import '../bloc/auth_state.dart';
import '../../core/constants/constants.dart';
import '../screens/profile/profile_screen.dart';

class ProfileDropdownAction extends StatelessWidget {
  const ProfileDropdownAction({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, state) {
        String? photoUrl;
        String name = 'User';
        if (state is Authenticated) {
          photoUrl = state.user.profilePhotoUrl;
          name = state.user.name;
        }

        final formattedUrl = AppConstants.formatImageUrl(photoUrl);

        final Widget avatar = formattedUrl != null
            ? CircleAvatar(
                radius: 16,
                backgroundImage: NetworkImage(formattedUrl),
              )
            : CircleAvatar(
                radius: 16,
                backgroundColor: Colors.white24,
                child: Text(
                  name.isNotEmpty ? name.substring(0, 1).toUpperCase() : 'U',
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
              );

        return PopupMenuButton<String>(
          offset: const Offset(0, 48),
          icon: avatar,
          tooltip: 'គណនី / Profile',
          onSelected: (value) {
            if (value == 'profile') {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => const ProfileScreen(),
                ),
              );
            } else if (value == 'logout') {
              context.read<AuthBloc>().add(LogoutRequested());
            }
          },
          itemBuilder: (BuildContext context) => <PopupMenuEntry<String>>[
            PopupMenuItem<String>(
              value: 'profile',
              child: Row(
                children: const [
                  Icon(Icons.person, color: Colors.blue),
                  SizedBox(width: 8),
                  Text('គណនីផ្ទាល់ខ្លួន / Profile'),
                ],
              ),
            ),
            const PopupMenuDivider(),
            PopupMenuItem<String>(
              value: 'logout',
              child: Row(
                children: const [
                  Icon(Icons.logout, color: Colors.red),
                  SizedBox(width: 8),
                  Text('ចាកចេញ / Logout'),
                ],
              ),
            ),
          ],
        );
      },
    );
  }
}
