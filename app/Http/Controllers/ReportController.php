<?php
namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ReportController extends Controller {
    public function index() {
        $stats = [
            'open'               => Report::where('status','open')->count(),
            'resolved_this_week' => Report::where('status','resolved')
                ->whereBetween('resolved_at',[now()->startOfWeek(),now()])->count(),
            'avg_resolution'     => '3.2h',
            'violation_issues'   => Report::where('priority','high')->where('status','open')->count(),
        ];
        $reports = Report::with('user')
            ->orderByRaw("FIELD(priority,'high','medium','low')")
            ->latest()
            ->paginate(20);
        return view('admin.reports.index', compact('stats','reports'));
    }

    public function show(Report $report) {
        $report->load('user');
        return view('admin.reports.show', compact('report'));
    }

    public function download(Report $report) {
        $report->load('user');

        $fileName = 'scholife-report-' . $report->id . '.docx';
        $path = storage_path('app/' . $fileName);
        $this->writeWordReport($report, $path);

        return response()->download($path, $fileName)->deleteFileAfterSend(true);
    }

    private function writeWordReport(Report $report, string $path): void {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Unable to create report document.');
        }

        $imagePath = $this->embeddedImagePath($report);
        $imageExtension = $imagePath ? strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) : null;
        $contentTypes = $this->contentTypesXml($imageExtension);
        $rels = $imagePath ? $this->documentRelsXml($imageExtension) : '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/_rels/document.xml.rels', $rels);
        $zip->addFromString('word/document.xml', $this->documentXml($report, $imagePath));

        if ($imagePath) {
            $zip->addFile($imagePath, 'word/media/report-image.' . ($imageExtension === 'png' ? 'png' : 'jpg'));
        }

        $zip->close();
    }

    private function embeddedImagePath(Report $report): ?string {
        if (! $report->attachment) {
            return null;
        }

        $path = Storage::disk('public')->path($report->attachment);
        if (! is_file($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png'], true) ? $path : null;
    }

    private function contentTypesXml(?string $imageExtension): string {
        $imageType = $imageExtension === 'png' ? '<Default Extension="png" ContentType="image/png"/>' : '<Default Extension="jpg" ContentType="image/jpeg"/><Default Extension="jpeg" ContentType="image/jpeg"/>';
        if (! $imageExtension) {
            $imageType = '';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>' . $imageType . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>';
    }

    private function documentRelsXml(?string $imageExtension): string {
        $extension = $imageExtension === 'png' ? 'png' : 'jpg';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdImage1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/report-image.' . $extension . '"/></Relationships>';
    }

    private function documentXml(Report $report, ?string $imagePath): string {
        $rows = [
            ['REPORT ID', '#' . $report->id],
            ['SUBJECT', $report->title],
            ['CATEGORY', $report->type],
            ['SUBMITTED BY', optional($report->user)->name ?? 'Mobile User'],
            ['PRIORITY', ucfirst($report->priority)],
            ['STATUS', ucfirst($report->status)],
            ['SUBMITTED AT', $report->created_at->format('M d, Y h:i A')],
            ['ATTACHMENT', $report->attachment ? basename($report->attachment) : 'None'],
            ['DESCRIPTION', $report->description ?: ''],
        ];

        $tableRows = '';
        foreach ($rows as [$label, $value]) {
            $tableRows .= '<w:tr><w:tc><w:tcPr><w:tcW w:w="2600" w:type="dxa"/></w:tcPr>' . $this->cellText($label, true) . '</w:tc><w:tc><w:tcPr><w:tcW w:w="7600" w:type="dxa"/></w:tcPr>' . $this->cellText($value, false) . '</w:tc></w:tr>';
        }

        $imageXml = $imagePath ? $this->imageXml($imagePath) : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><w:body><w:p><w:r><w:rPr><w:b/><w:color w:val="8B1C2C"/><w:sz w:val="34"/></w:rPr><w:t>Scholife Report</w:t></w:r></w:p><w:p><w:r><w:rPr><w:color w:val="666666"/><w:sz w:val="20"/></w:rPr><w:t>Generated ' . $this->xml(now()->format('M d, Y h:i A')) . '</w:t></w:r></w:p><w:p/><w:tbl><w:tblPr><w:tblW w:w="10200" w:type="dxa"/><w:tblBorders><w:bottom w:val="single" w:sz="4" w:space="0" w:color="F0DADC"/><w:insideH w:val="single" w:sz="4" w:space="0" w:color="F0DADC"/></w:tblBorders></w:tblPr>' . $tableRows . '</w:tbl>' . $imageXml . '<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="900" w:right="900" w:bottom="900" w:left="900"/></w:sectPr></w:body></w:document>';
    }

    private function cellText(string $text, bool $label): string {
        $color = $label ? '666666' : '111111';
        $bold = $label ? '<w:b/>' : '';
        return '<w:p><w:r><w:rPr>' . $bold . '<w:color w:val="' . $color . '"/><w:sz w:val="20"/></w:rPr><w:t>' . $this->xml($text) . '</w:t></w:r></w:p>';
    }

    private function imageXml(string $imagePath): string {
        [$width, $height] = getimagesize($imagePath) ?: [900, 600];
        $maxWidth = 5486400;
        $ratio = $height > 0 ? $height / max($width, 1) : 0.66;
        $cx = $maxWidth;
        $cy = (int) ($maxWidth * $ratio);

        return '<w:p/><w:p><w:r><w:rPr><w:b/><w:color w:val="666666"/><w:sz w:val="20"/></w:rPr><w:t>ATTACHED IMAGE</w:t></w:r></w:p><w:p><w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="' . $cx . '" cy="' . $cy . '"/><wp:docPr id="1" name="Report Attachment"/><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="0" name="Report Attachment"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="rIdImage1"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>';
    }

    private function xml(?string $value): string {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}



