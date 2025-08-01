namespace App\Repositories;

use App\Models\KelompokKasOrBank;
use App\Models\KelompokAktiva;
use Illuminate\Support\Facades\Log;

class GlobalRepository
{
    public function deleteKelompokKasOrBank($id, $type)
    {
        try {
            // Assuming you have a model named KelompokKasOrBank
            return KelompokKasOrBank::where('id', $id)->where('type', $type)->delete();
        } catch (\Exception $e) {
            // Log the error and return false
            Log::error('Error deleting KelompokKasOrBank: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteKelompokAktiva($id)
    {
        try {
            // Assuming you have a model named KelompokAktiva
            return KelompokAktiva::where('id', $id)->delete();
        } catch (\Exception $e) {
            // Log the error and return false
            Log::error('Error deleting KelompokAktiva: ' . $e->getMessage());
            return false;
        }
    }
}