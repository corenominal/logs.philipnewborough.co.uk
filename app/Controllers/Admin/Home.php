<?php

namespace App\Controllers\Admin;

use Hermawan\DataTables\DataTable;

class Home extends BaseController
{
    /**
     * Display the Admin Event Viewer dashboard page.
     *
     * Prepares view data for the admin home screen, including:
     * - Enabling DataTables assets
     * - Registering page-specific JavaScript and CSS assets
     * - Setting the page title
     *
     * @return string Rendered `admin/home` view output.
     */
    public function index()
    {
        // Use datatables JS/CSS
        $data['datatables'] = true;
        // Array of javascript files to include
        $data['js'] = [
            'admin/home',
        ];
        // Array of CSS files to include
        $data['css'] = [];
        // Page title
        $data['title'] = 'Event Viewer';

        $model = model('LogsModel');

        $counts = ['total' => 0, 'info' => 0, 'warning' => 0, 'error' => 0, 'critical' => 0, 'debug' => 0];
        $counts['total'] = $model->countAllResults(false);

        $rows = $model->select('level, COUNT(*) as count')->groupBy('level')->findAll();
        $levelMap = [0 => 'info', 1 => 'warning', 2 => 'error', 3 => 'critical', 4 => 'debug'];
        foreach ($rows as $row) {
            $key = $levelMap[$row['level']] ?? null;
            if ($key) {
                $counts[$key] = (int) $row['count'];
            }
        }

        $data['stats'] = $counts;

        return view('admin/home', $data);
    }

    public function datatable()
    {
        $model = model('LogsModel');
        $model->select('
            id,
            level,
            message,
            domain,
            created_at
        ');

        $level = $this->request->getGet('level');
        if ($level !== null && $level !== '') {
            $model->where('level', (int) $level);
        }

        return DataTable::of($model)
            ->add('DT_RowId', static fn ($row) => $row->id)
            ->edit('created_at', function($row){
                // Format the timezone based on user's timezone query parameter
                $timezone = $this->request->getGet('timezone') ?? 'UTC';
                $date = new \DateTime($row->created_at, new \DateTimeZone('UTC'));
                $date->setTimezone(new \DateTimeZone($timezone));
                $row->created_at = $date->format('Y-m-d H:i:s');
                // Return the formatted date with a span for styling
                return '<span class="text-nowrap">' . $row->created_at . '</span>';
            })
            ->edit('level', function($row){
                switch ($row->level) {
                    case 0:
                        return '<span class="badge bg-info"><i class="bi bi-info-circle-fill me-1"></i>Info</span>';
                    case 1:
                        return '<span class="badge bg-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i>Warning</span>';
                    case 2:
                        return '<span class="badge bg-danger"><i class="bi bi-exclamation-octagon-fill me-1"></i>Error</span>';
                    case 3:
                        return '<span class="badge bg-danger"><i class="bi bi-exclamation-octagon-fill me-1"></i>Critical</span>';
                    case 4:
                        return '<span class="badge bg-primary"><i class="bi bi-bug-fill me-1"></i>Debug</span>';
                    default:
                        return '<span class="badge bg-secondary">Unknown</span>';
                }
            })
            ->hide('id')
            ->toJson(true);
    }

    public function deleteLogs()
    {
        $body = $this->request->getJSON(true);
        $ids  = array_values(array_filter(
            array_map('intval', (array) ($body['ids'] ?? [])),
            fn($id) => $id > 0
        ));

        if (empty($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No valid IDs provided']);
        }

        model('LogsModel')->whereIn('id', $ids)->delete();

        return $this->response->setJSON(['deleted' => count($ids)]);
    }

    public function stats()
    {
        $model = model('LogsModel');
        $counts = ['total' => 0, 'info' => 0, 'warning' => 0, 'error' => 0, 'critical' => 0, 'debug' => 0];
        $counts['total'] = $model->countAllResults(false);

        $rows = $model->select('level, COUNT(*) as count')->groupBy('level')->findAll();
        $levelMap = [0 => 'info', 1 => 'warning', 2 => 'error', 3 => 'critical', 4 => 'debug'];
        foreach ($rows as $row) {
            $key = $levelMap[$row['level']] ?? null;
            if ($key) {
                $counts[$key] = (int) $row['count'];
            }
        }

        return $this->response->setJSON($counts);
    }
}
