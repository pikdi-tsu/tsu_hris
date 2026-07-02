<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Exports\NotificationBackupExport;
use Maatwebsite\Excel\Facades\Excel;

class NotificationController extends Controller
{
    /**
     * Display a listing of all notifications for the current user.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter', 'unread');
        
        if ($filter == 'read') {
            $notifications = $user->readNotifications()->paginate(15);
        } elseif ($filter == 'all') {
            $notifications = $user->notifications()->paginate(15);
        } else {
            // Default: unread
            $notifications = $user->unreadNotifications()->paginate(15);
        }
        
        $notifications->appends(['filter' => $filter]);
        
        $unreadCount = $user->unreadNotifications()->count();
        $readCount = $user->readNotifications()->count();
        $title = 'Kotak Masuk Notifikasi';
        
        return view('system::notifications.index', compact('notifications', 'filter', 'unreadCount', 'readCount', 'title'));
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
    
    /**
     * Backup read notifications to Excel and clear them from DB.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function backupAndClear()
    {
        $user = Auth::user();
        $readNotifs = $user->readNotifications()->get();
        
        if ($readNotifs->count() == 0) {
            return redirect()->back()->with('error', 'Tidak ada riwayat notifikasi yang bisa di-backup.');
        }

        // Export to Excel
        $filename = 'Backup_Notifikasi_' . $user->name . '_' . date('Ymd_His') . '.xlsx';
        $export = new NotificationBackupExport($readNotifs);
        
        // After generating the file, we can't easily delete right after return Excel::download because it halts execution.
        // But Excel::download returns a response. We can do it before, by saving to disk, downloading, and then deleting.
        // Wait, a simpler way is to delete them first since we already loaded them into memory ($readNotifs).
        
        $user->readNotifications()->delete();
        
        return Excel::download($export, $filename);
    }
}
