<?php

namespace App\Exports;

use App\Models\Clients;
use App\Models\TeamMembers;
use Auth;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StsExport extends  DefaultValueBinder implements FromArray , WithHeadings , WithColumnFormatting, ShouldAutoSize, WithCustomValueBinder
{

    /**
    * @return \Illuminate\Support\Collection
    */
    public $reqDatas;
    function __construct( $request) {
            $this->reqDatas = $request;
    }

    public function array():array
    {
        $category = $this->reqDatas->category;
        $from_date = $this->reqDatas->from_date;
        $employee = $this->reqDatas->employee;
        $searchCat = $this->reqDatas->searchCategory;

        $dates = explode(" - ", $from_date);
        $from  = $dates[0];
        $to    = $dates[1];

        $frms = Carbon::createFromFormat('d/m/Y',$from)->format('Y-m-d');
        $toos   = Carbon::createFromFormat('d/m/Y', $to)->format('Y-m-d');
        $todt = Carbon::parse($toos)->addDays(1);

        $user  = Auth::user();

        if($employee == 'All'){
            if($user->hasRole('Team-Leader')){
                $teams  =  DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
                $allmem =  TeamMembers::with('users.roles')
                                        ->whereHas('users.roles', function($query){
                                            $query->where('name', 'Sales-Executive');
                                        })
                                        ->whereIn('team', $teams)->where('status', true)->pluck('user')->toArray();

                array_push($allmem, $user->id);

                $clients = Clients::filterStatus($category, $searchCat)
                                    ->whereHas('history', function($query) use($allmem,  $frms, $todt, $category, $searchCat){
                                        $query->whereIn('created',  $allmem);
                                        $query->where('category',  $searchCat);
                                        $query->filterStatus($category, $searchCat);
                                        $query->whereBetween("created_at",[ $frms, $todt]);
                                    })
                                    ->with(['history' => function($query) use($allmem,  $frms, $todt, $category, $searchCat){
                                            $query->whereIn('created',   $allmem);
                                            $query->where('category',  $searchCat);
                                            $query->filterStatus($category, $searchCat);
                                            $query->whereBetween("created_at",[ $frms, $todt]);
                                            $query->select('remarks','created_at' , 'category', 'tbro_type', 'time', 'tbro','remarks', 'client', );
                                    }])
                                    ->with('telereferral:id,name', 'referral:id,name')
                                    ->whereIn('tele_ref_user', $allmem)->get();
            }

            if($user->hasRole('Admin')){
                $eloquent = Clients::whereHas('history', function($query) use( $frms, $todt, $category,$searchCat){
                                $query->where('category',  $searchCat);
                                $query->filterStatus($category, $searchCat);
                                $query->whereBetween("created_at",[ $frms, $todt]);
                            })
                            ->with(['history' => function($query) use(  $frms, $todt, $category, $searchCat){
                                    $query->where('category',  $searchCat);
                                    $query->filterStatus($category, $searchCat);
                                    $query->whereBetween("created_at",[ $frms, $todt]);
                            }]);

                $clients = $eloquent->filterStatus($category, $searchCat)->get();
            }

        }else{

            if($user->hasRole('Team-Leader') || $user->hasRole('Admin')){
                $eloquent = Clients::whereHas('history', function($query) use($employee,  $frms, $todt, $category, $searchCat){
                                        $query->where('created',  $employee);
                                        $query->where('category',  $searchCat);
                                        $query->filterStatus($category, $searchCat);
                                        $query->whereBetween("created_at",[ $frms, $todt]);
                                    })
                                    ->with(['history' => function($query) use($employee,  $frms, $todt, $category, $searchCat){
                                            $query->where('created',  $employee);
                                            $query->where('category',  $searchCat);
                                            $query->filterStatus($category, $searchCat);
                                            $query->whereBetween("created_at",[ $frms, $todt]);
                                    }])
                                    ->where(function ($query) use($employee) {
                                            $query->where('tele_ref_user', $employee);
                                    });
            }else{
                $eloquent = Clients::whereHas('history', function($query) use($employee,  $frms, $todt, $category, $searchCat){
                                                $query->where('created',  $employee);
                                                $query->where('category',  $searchCat);
                                                $query->filterStatus($category, $searchCat);
                                                $query->whereBetween("created_at",[ $frms, $todt]);
                                            })
                                            ->with(['history' => function($query) use($employee,  $frms, $todt, $category, $searchCat){
                                                    $query->where('created',  $employee);
                                                    $query->where('category',  $searchCat);
                                                    $query->filterStatus($category, $searchCat);
                                                    $query->whereBetween("created_at",[ $frms, $todt]);
                                            }])
                                            ->where('ref_user', $employee);
            }
            $clients = $eloquent->filterStatus($category, $searchCat)->get();
        }

        $arrClients = array();
        $ctr = 0;
        foreach($clients as $client){
            $innerArray = array();
            $innerArray['sl no']                    = $ctr+1;
            $innerArray['client']                   = $client->name;
            $innerArray['client-category']          = $client->category;
            $innerArray['contact-person']           = $client->cont_person;
            $innerArray['contactinfo']              = $client->mobile;
            $innerArray['telerefuser']              = $client->telereferral->name;
            $innerArray['salesexec']                = $client->referral->name;
            $innerArray['status']                   = $client->status;
            $innerArray['history-category']         = $client->history->category;
            $innerArray['type']                     = $client->history->tbro_type;
            $innerArray['time']                     = $client->history->time;
            $innerArray['tbro']                     = $client->history->tbro;
            $innerArray['remarks']                  = $client->history->remarks;
            $innerArray['stsadded']                 = Carbon::parse($client->history->created_at)->format('d M Y');

            array_push($arrClients, $innerArray);
            $ctr++;
        }
        return $arrClients;

    }


    public function headings(): array
    {
        return ["Sl No", "Client Name", "Category", "Contact Person", "Contact Info", "Tele Ref Exe.","Sales Exe.",
                    "Client Status",'category', 'Tbro Type', 'Time', 'TBRO', "Remarks", 'Added Date'];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function bindValue(Cell $cell, $value){
        if ($cell->getColumn() == 'H') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        // else return default behavior
        return parent::bindValue($cell, $value);
  }

}
