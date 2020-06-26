<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Company;
use App\Rates;
use Illuminate\Support\Facades\DB;
use App\Xml\Array2XML;

class Report extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a report';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $omega_tv[] = $this->getFirstRequest('OmegaTV');
        $omega_tv[] = $this->getSecondRequest('OmegaTV');
        $omega_tv[] = $this->getThirdRequest('OmegaTV');
        $omega_tv[] = $this->getFourthRequest('OmegaTV');

        $viasat[] = $this->getFirstRequest('Viasat');
        $viasat[] = $this->getSecondRequest('Viasat');
        $viasat[] = $this->getThirdRequest('Viasat');
        $viasat[] = $this->getFourthRequest('Viasat');

        $this->createExcelFail('OmegaTV', $omega_tv);
        $this->createCsvFile('OmegaTV', $omega_tv);
        $this->createXmlFile('OmegaTV', $omega_tv);
        $this->createJsonFile('OmegaTV', $omega_tv);

        $this->createExcelFail('Viasat', $viasat);
        $this->createCsvFile('Viasat', $viasat);
        $this->createXmlFile('Viasat', $viasat);
        $this->createJsonFile('Viasat', $viasat);
    }

    public function getFirstRequest($name)
    {
        $request = Company::with('customer')
            ->where('company', '=', $name)->get();

        foreach ($request as $reques) {
            $result[] = [
                'company' => $reques->company,
                'sum_customer' => $reques->customer->count()
            ];
        }

        return $result;
    }

    public function getSecondRequest($name)
    {
        $request = Company::all()->where('company', '=', $name);

        foreach ($request as $reques) {
            $result[] = [
                'company' => $reques->company,
                'sum_not_active_customer' => $reques->customer()
                    ->where('state', '=', 'off')->count()
            ];
        }

        return $result;
    }

    public function getThirdRequest($name)
    {
        $request = Rates::with(['company']);

        $request = $request->withCount(['customer' =>
            function ($query) {
                $query->where('state', 'like', 'on');
            }]);

        $request = $request->whereHas('company',
            function ($query) use ($name) {
                $query->where('company', 'like', $name);
            })->get()->toArray();

        foreach ($request as $tariff) {
            $result[] = [
                'tariff' => $tariff['tariff'],
                'customer_count' => $tariff['customer_count']
            ];
        }

        return $result;
    }

    public function getFourthRequest($name)
    {
        $request = DB::table('customer')->select('surname', 'first_name',
            'rates.tariff')
            ->join('rates', 'customer.rates_id', '=', 'rates.id')
            ->whereIn('rates_id', DB::table('rates')->select('id')
                ->whereIn('company_id', DB::table('the_company')->select('id')
                    ->where('company', '=', $name)))
            ->where('state', '=', 'on')
            ->get()->toArray();

        foreach ($request as $reques) {
            $result[] = ['surname' => $reques->surname,
                'first_name' => $reques->first_name,
                'tariff' => $reques->tariff
            ];
        }

        return $result;
    }

    public function createExcelFail($name, array $arr_request)
    {
        $document = new \PHPExcel();

        $sheet = $document->setActiveSheetIndex(0); // Выбираем первый лист в документе

        $columnPosition = 0; // Начальная координата x
        $startLine = 2; // Начальная координата y

        foreach ($arr_request as $list){
            $catList = $list;

            // Перекидываем указатель на следующую строку
            $startLine++;

            // Массив с названиями столбцов

            foreach ($catList as $arr){
                $columns = array_keys($arr);
            }

            array_unshift($columns, "№");

            // Указатель на первый столбец
            $currentColumn = $columnPosition;

            // Формируем шапку
            foreach ($columns as $column) {
                // Красим ячейку
                $sheet->getStyleByColumnAndRow($currentColumn, $startLine)
                    ->getFill()
                    ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('4dbf62');

                $sheet->setCellValueByColumnAndRow($currentColumn, $startLine, $column);

                // Смещаемся вправо
                $currentColumn++;
            }

            // Формируем список
            foreach ($catList as $key => $catItem) {
                // Перекидываем указатель на следующую строку
                $startLine++;
                // Указатель на первый столбец
                $currentColumn = $columnPosition;
                // Вставляем порядковый номер
                $sheet->setCellValueByColumnAndRow($currentColumn,
                    $startLine, $key + 1);

                // Ставляем информацию об имени и цвете
                foreach ($catItem as $value) {
                    $currentColumn++;
                    $sheet->setCellValueByColumnAndRow($currentColumn, $startLine, $value);
                }
            }
            $startLine++;
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($document, 'Excel5');
        $objWriter->save($name."-".date("Y-m-d").".xls");
    }

    public function createCsvFile ($name, array $result)
    {
        $counter = 1;
        $footer = ['*********************************************************************************'];

        $file_csv = fopen($name.'-'.date("Y-m-d").'.csv', 'w');

        foreach ($result as $answer) {

            $list = $answer;
            $title = ["Request № ".$counter];
            fputcsv($file_csv, $title);

            foreach ($list as $fields) {
                fputcsv($file_csv, $fields);
            }

            fputcsv($file_csv, $footer);
            $counter++;
        }
        fclose($file_csv);
    }

    public function createXmlFile($name, array $result)
    {
        header('Content-type: application/xml');

        $converter = new Array2XML();
        $xml_array = $converter->convert($result);

        $xml_file = fopen($name.'-'.date("Y-m-d").'.xml','w');
        fwrite($xml_file,$xml_array);
        fclose($xml_file);
    }

    public function createJsonFile($name, array $result)
    {
        $json_array = json_encode($result, JSON_UNESCAPED_UNICODE);

        $json_file = fopen($name.'-'.date("Y-m-d").'.json','w');
        fwrite($json_file,$json_array);
        fclose($json_file);
    }
}
