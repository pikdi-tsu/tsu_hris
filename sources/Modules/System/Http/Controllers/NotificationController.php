<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of all notifications for the current user.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        // Get all notifications with pagination
        $notifications = $user->notifications()->paginate(15);
        
        return view('system::notifications.index', compact('notifications'));
    }

    /**
     * Mark a specific notification as read and redirect appropriately.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function read($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            
            // Extract download URL if it exists
            if (isset($notification->data['download_url'])) {
                return redirect($notification->data['download_url']);
            }
        }

        return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }
    
    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
