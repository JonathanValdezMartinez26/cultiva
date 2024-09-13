<?php

namespace Core;

defined("APPPATH") or die("Access denied");

class Controller
{
    public $__usuario = '';
    public $__nombre = '';
    public $__puesto = '';
    public $__cdgco = '';
    public $__perfil = '';

    public function __construct()
    {
        session_start();
        if ($_SESSION['usuario'] == '' || empty($_SESSION['usuario'])) {
            unset($_SESSION);
            session_unset();
            session_destroy();
            header("Location: /Login/");
            exit();
        } else {
            $this->__usuario = $_SESSION['usuario'];
            $this->__nombre = $_SESSION['nombre'];
            $this->__puesto = $_SESSION['puesto'];
            $this->__cdgco = $_SESSION['cdgco'];
            $this->__perfil = $_SESSION['perfil'];
        }
    }

    public function GetExtraHeader($titulo, $elementos = [])
    {
        $html = <<<HTML
        <title>$titulo</title>
        HTML;

        if (!empty($elementos)) {
            foreach ($elementos as $elemento) {
                $html .= "\n" . $elemento;
            }
        }

        return $html;
    }

    public function ColumnaExcel($letra, $campo, $titulo = '', $estilo = [])
    {
        $titulo = $titulo == '' ? $campo : $titulo;

        return [
            'letra' => $letra,
            'campo' => $campo,
            'estilo' => $estilo,
            'titulo' => $titulo
        ];
    }

    public function GetEstilosExcel()
    {
        return [
            'titulo' => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]
                ]
            ],
            'centrado' => [
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ],
            'moneda' => [
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT],
                'numberformat' => ['code' => \PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE]
            ],
            'fecha' => [
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
                'numberformat' => ['code' => \PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY]
            ],
            'fecha_hora' => [
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
                'numberformat' => ['code' => \PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME]
            ],
        ];
    }

    public function GeneraExcel($nombre_archivo, $nombre_hoja, $titulo_reporte, $columnas, $filas)
    {
        $excel = new \PHPExcel();
        $excel->getProperties()->setCreator("Sistema MCM");
        $excel->getProperties()->setLastModifiedBy("Sistema MCM");
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->setTitle($nombre_hoja);

        $excel->getActiveSheet()->SetCellValue('A1', $titulo_reporte);
        $excel->getActiveSheet()->mergeCells('A1:' . $columnas[count($columnas) - 1]['letra'] . '1');
        $excel->getActiveSheet()->getStyle('A1')->applyFromArray(self::GetEstilosExcel()['titulo']);

        foreach ($columnas as $key => $columna) {
            $excel->getActiveSheet()->SetCellValue($columna['letra'] . '2', $columna['titulo']);
            $excel->getActiveSheet()->getStyle($columna['letra'] . '2')->applyFromArray(self::GetEstilosExcel()['titulo']);
            $excel->getActiveSheet()->getColumnDimensionByColumn($key)->setAutoSize(true);
        }

        $noFila = 3;
        foreach ($filas as $key => $fila) {
            if ($noFila % 2 == 0) {
                $excel->getActiveSheet()->getStyle('A' . $noFila . ':' . $columnas[count($columnas) - 1]['letra'] . $noFila)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
            }

            foreach ($columnas as $key => $columna) {
                $estiloCelda = $columna['estilo'];
                $estiloCelda['borders']['left']['style'] = \PHPExcel_Style_Border::BORDER_THIN;
                $estiloCelda['borders']['right']['style'] = \PHPExcel_Style_Border::BORDER_THIN;
                
                $excel->getActiveSheet()->SetCellValue($columna['letra'] . $noFila, html_entity_decode($fila[$columna['campo']], ENT_QUOTES, "UTF-8"));
                $excel->getActiveSheet()->getStyle($columna['letra'] . $noFila)->applyFromArray($estiloCelda);
            }

            $noFila += 1;
        }

        $excel->getActiveSheet()->setSelectedCell('A1');
        $excel->getActiveSheet()->freezePane('A3');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombre_archivo . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Pragma: public');

        \PHPExcel_Settings::setZipClass(\PHPExcel_Settings::PCLZIP);
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');
    }
}
