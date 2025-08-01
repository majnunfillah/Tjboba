<?php

namespace App\View\Components;

use App\Models\DBMENU;
use Illuminate\View\Component;

class Sidebar extends Component
{
    private $mapping;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->mapping = [
            ["KODEMENU" => "00", "route" => "berkas*", 'icon' => "fa-file"],
            ["KODEMENU" => "0001", "route" => "#setPeriode"],
            ["KODEMENU" => "0002", "route" => NULL],
            ["KODEMENU" => "0003", "route" => "berkas.perusahaan.index"],
            ["KODEMENU" => "00031", "route" => NULL],
            ["KODEMENU" => "0004", "route" => "berkas.set-pemakai.index"],
            ["KODEMENU" => "0005", "route" => "#gantiPassword"],
            ["KODEMENU" => "0006", "route" => NULL],
            ["KODEMENU" => "0007", "route" => NULL],
            ["KODEMENU" => "0008", "route" => "#logOut"],
            ["KODEMENU" => "01", "route" => "master-data*", 'icon' => "fa-database"],
            ["KODEMENU" => "01001", "route" => "master_accounting*"],
            ["KODEMENU" => "01001001", "route" => "master-data.master-accounting.perkiraan.index"],
            ["KODEMENU" => "01001002", "route" => "master-data.master-accounting.aktiva.index"],
            ["KODEMENU" => "010010021", "route" => NULL],
            ["KODEMENU" => "01001003", "route" => NULL],
            ["KODEMENU" => "01001004", "route" => NULL],
            ["KODEMENU" => "01001005", "route" => NULL],
            ["KODEMENU" => "01001006", "route" => NULL],
            ["KODEMENU" => "01001007", "route" => NULL],
            ["KODEMENU" => "01001008", "route" => "master-data.master-accounting.posting.index"],
            ["KODEMENU" => "01001009", "route" => NULL],
            ["KODEMENU" => "010010091", "route" => NULL],
            ["KODEMENU" => "01002", "route" => "master"],
            ["KODEMENU" => "01002013", "route" => NULL],
            ["KODEMENU" => "01002015", "route" => "master-data.master-bahan-dan-barang.group.index"],
            ["KODEMENU" => "01002016", "route" => NULL],
            ["KODEMENU" => "01002017", "route" => NULL],
            ["KODEMENU" => "01002018", "route" => NULL],
            ["KODEMENU" => "01002021", "route" => NULL],
            ["KODEMENU" => "01002022", "route" => NULL],
            ["KODEMENU" => "01003", "route" => NULL],
            ["KODEMENU" => "01003001", "route" => NULL],
            ["KODEMENU" => "01003002", "route" => NULL],
            ["KODEMENU" => "01003005", "route" => NULL],
            ["KODEMENU" => "010030055", "route" => NULL],
            ["KODEMENU" => "01003010", "route" => NULL],
            ["KODEMENU" => "01004", "route" => NULL],
            ["KODEMENU" => "01004000", "route" => NULL],
            ["KODEMENU" => "01004005", "route" => NULL],
            ["KODEMENU" => "01004006", "route" => NULL],
            ["KODEMENU" => "01004007", "route" => NULL],
            ["KODEMENU" => "010040081", "route" => NULL],
            ["KODEMENU" => "01005", "route" => NULL],
            ["KODEMENU" => "0100502", "route" => NULL],
            ["KODEMENU" => "0100505", "route" => NULL],
            ["KODEMENU" => "02", "route" => "accounting*", "icon" => "fa-money-bill-wave"],
            ["KODEMENU" => "02001", "route" => "accounting.bank-or-kas.index"],
            ["KODEMENU" => "02002", "route" => "accounting.memorial.index"],
            ["KODEMENU" => "02003", "route" => NULL],
            ["KODEMENU" => "03", "route" => NULL],
            ["KODEMENU" => "03001", "route" => NULL],
            ["KODEMENU" => "03002", "route" => NULL],
            ["KODEMENU" => "03003", "route" => NULL],
            ["KODEMENU" => "0300301", "route" => NULL],
            ["KODEMENU" => "03004", "route" => NULL],
            ["KODEMENU" => "03005", "route" => NULL],
            ["KODEMENU" => "03006", "route" => NULL],
            ["KODEMENU" => "030061", "route" => NULL],
            ["KODEMENU" => "03007", "route" => NULL],
            ["KODEMENU" => "03008", "route" => NULL],
            ["KODEMENU" => "03009", "route" => NULL],
            ["KODEMENU" => "03010", "route" => NULL],
            ["KODEMENU" => "04", "route" => NULL],
            ["KODEMENU" => "04001", "route" => NULL],
            ["KODEMENU" => "040011", "route" => NULL],
            ["KODEMENU" => "040012", "route" => NULL],
            ["KODEMENU" => "04002", "route" => NULL],
            ["KODEMENU" => "04003", "route" => NULL],
            ["KODEMENU" => "04004", "route" => NULL],
            ["KODEMENU" => "040041", "route" => NULL],
            ["KODEMENU" => "04006", "route" => NULL],
            ["KODEMENU" => "04007", "route" => NULL],
            ["KODEMENU" => "04008", "route" => NULL],
            ["KODEMENU" => "04010", "route" => NULL],
            ["KODEMENU" => "05", "route" => "produksi*", "icon" => "fa-cogs"],
            ["KODEMENU" => "05001", "route" => "produksi.spk.index"],
            ["KODEMENU" => "050011", "route" => NULL],
            ["KODEMENU" => "050012", "route" => NULL],
            ["KODEMENU" => "05002", "route" => NULL],
            ["KODEMENU" => "05003", "route" => NULL],
            ["KODEMENU" => "05004", "route" => NULL],
            ["KODEMENU" => "05005", "route" => NULL],
            ["KODEMENU" => "06", "route" => NULL],
            ["KODEMENU" => "0600101", "route" => NULL],
            ["KODEMENU" => "06004", "route" => NULL],
            ["KODEMENU" => "06005", "route" => NULL],
            ["KODEMENU" => "06010", "route" => NULL],
            ["KODEMENU" => "06011", "route" => NULL],
            ["KODEMENU" => "0601101", "route" => NULL],
            ["KODEMENU" => "06013", "route" => NULL],
            ["KODEMENU" => "06014", "route" => NULL],
            ["KODEMENU" => "07", "route" => NULL],
            ["KODEMENU" => "0701", "route" => NULL],
            ["KODEMENU" => "08", "route" => "laporan-laporan.*", "icon" => "fa-file-excel"],
            ["KODEMENU" => "08001", "route" => "laporan-laporan.view-laporan"],
            ["KODEMENU" => "081", "route" => NULL],
            ["KODEMENU" => "08101", "route" => NULL],
            ["KODEMENU" => "08102", "route" => "utilitas.hitung-ulang-hpp.index"],
            ["KODEMENU" => "08103", "route" => NULL],
            ["KODEMENU" => "08104", "route" => "utilitas.tutup-buku.index"],
            ["KODEMENU" => "081041", "route" => NULL],
            ["KODEMENU" => "081051", "route" => NULL],
            ["KODEMENU" => "081052", "route" => NULL],
            ["KODEMENU" => "09", "route" => NULL],
            ["KODEMENU" => "0901", "route" => NULL],
            ["KODEMENU" => "0902", "route" => NULL],
            ["KODEMENU" => "09021", "route" => NULL],
            ["KODEMENU" => "09022", "route" => NULL],
            ["KODEMENU" => "0903", "route" => NULL]
        ];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $menus = DBMENU::where('L0', 0)->whereHas('checkAccess', function ($q) {
            $q->where('USERID', auth()->user()->USERID)
                ->where('HASACCESS', 1);
        })->get()->map(function ($map) {
            $submenus = DBMENU::where('KODEMENU', 'LIKE', "$map->KODEMENU%")->where('L0', 1)->whereHas('checkAccess', function ($q) {
                $q->where('USERID', auth()->user()->USERID)
                    ->where('HASACCESS', 1);
            })->get()->filter(function ($fill) {
                if ($fill->routename === NULL) {
                    $fill->icon = 'fa-times-circle';
                }

                $submenus = DBMENU::where('KODEMENU', 'LIKE', "$fill->KODEMENU%")->where('L0', 2)->whereHas('checkAccess', function ($q) {
                    $q->where('USERID', auth()->user()->USERID)
                        ->where('HASACCESS', 1);
                })->get()->filter(function ($fil) {
                    if ($fil->routename === NULL) {
                        $fil->icon = 'fa-times-circle';
                    }
                    return $fil;
                });
                if (count($submenus) > 0) {
                    $fill->submenu = $submenus;
                } else {
                    $fill->submenu = [];
                }

                return $fill;
            });
            if (count($submenus) > 0) {
                $map->submenu = $submenus;
            } else {
                $map->submenu = [];
            }
            return $map;
        });

        return view('components.sidebar', ['menus' => $menus]);
    }
}
