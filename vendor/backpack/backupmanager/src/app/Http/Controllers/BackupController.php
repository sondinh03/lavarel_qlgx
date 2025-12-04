<?php

namespace Backpack\BackupManager\app\Http\Controllers;

use Artisan;
use Exception;
use Illuminate\Routing\Controller;
use Log;
use Request;
use Response;
use Storage;

class BackupController extends Controller
{
    public function index()
    {
        if (!count(config('backup.backup.destination.disks'))) {
            dd(trans('backpack::backup.no_disks_configured'));
        }

        $this->data['backups'] = [];

        foreach (config('backup.backup.destination.disks') as $disk_name) {
            $disk = Storage::disk($disk_name);
            $can_download = config("filesystems.disks.$disk_name.driver") == 'local';
            $files = $disk->allFiles();

            // make an array of backup files, with their filesize and creation date
            foreach ($files as $k => $f) {
                // only take the zip files into account
                if (substr($f, -4) == '.zip' && $disk->exists($f)) {
                    $this->data['backups'][] = [
                        'file_path'     => $f,
                        'file_name'     => str_replace('backups/', '', $f),
                        'file_size'     => $disk->size($f),
                        'last_modified' => $disk->lastModified($f),
                        'disk'          => $disk_name,
                        'download'      => $can_download ? true : false,
                    ];
                }
            }
        }

        // reverse the backups, so the newest one would be on top
        $this->data['backups'] = array_reverse($this->data['backups']);
        $this->data['title'] = 'Backups';

        return view('backupmanager::backup', $this->data);
    }

    public function create()
    {
        $message = 'success';

        try {
            ini_set('max_execution_time', 600);

            Log::info('Backpack\BackupManager -- Called backup:run from admin interface');

            Artisan::call('backup:run');

            $output = Artisan::output();
            if (strpos($output, 'Backup failed because')) {
                preg_match('/Backup failed because(.*?)$/ms', $output, $match);
                $message = "Backpack\BackupManager -- backup process failed because ";
                $message .= isset($match[1]) ? $match[1] : '';
                Log::error($message.PHP_EOL.$output);
            } else {
                Log::info("Backpack\BackupManager -- backup process has started");
            }
        } catch (Exception $e) {
            Log::error($e);

            return Response::make($e->getMessage(), 500);
        }

        return $message;
    }

    /**
     * Downloads a backup zip file.
     */
    public function download()
    {
        $disk_name = Request::input('disk');
        $disk = Storage::disk($disk_name);
        $file_name = Request::input('file_name');
        $can_download = config("filesystems.disks.$disk_name.driver") == 'local';

        if ($can_download) {
            if ($disk->exists($file_name)) {
                if (method_exists($disk->getAdapter(), 'getPathPrefix')) {
                    $storage_path = $disk->getAdapter()->getPathPrefix();

                    return response()->download($storage_path.$file_name);
                } else {
                    return $disk->download($file_name);
                }
            } else {
                abort(404, trans('backpack::backup.backup_doesnt_exist'));
            }
        } else {
            abort(404, trans('backpack::backup.only_local_downloads_supported'));
        }
    }

    /**
     * Deletes a backup file.
     */
    public function delete($file_name)
    {
        $diskName = Request::input('disk');

        if (!in_array($diskName, config('backup.backup.destination.disks'))) {
            abort(500, trans('backpack::backup.unknown_disk'));
        }

        $disk = Storage::disk($diskName);

        if ($disk->exists($file_name)) {
            $disk->delete($file_name);

            return 'success';
        } else {
            abort(404, trans('backpack::backup.backup_doesnt_exist'));
        }
    }
}
