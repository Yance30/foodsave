<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Pilih lokasi (kecamatan atau GPS browser) untuk jarak & urutan "Terdekat".
 */
class LocationController extends Controller
{
    public function update(Request $request)
    {
        $districts = config('foodsave.districts');

        $data = $request->validate([
            'district' => ['nullable', 'in:'.implode(',', array_keys($districts))],
            'lat' => ['nullable', 'required_without:district', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'required_without:district', 'numeric', 'between:-180,180'],
            'save_default' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['district'])) {
            [$lat, $lng] = $districts[$data['district']];
            $label = $data['district'].', Malang';
        } else {
            $lat = (float) $data['lat'];
            $lng = (float) $data['lng'];
            $label = 'Lokasi saat ini';
        }

        $request->session()->put('foodsave.location', compact('lat', 'lng', 'label'));

        if ($request->boolean('save_default') && $request->user()) {
            $request->user()->update(['lat' => $lat, 'lng' => $lng, 'location_label' => $label]);

            return back()->with('success', 'Lokasi default disimpan: '.$label.'.');
        }

        return back()->with('success', 'Lokasi diatur ke '.$label.'. Listing kini diurutkan dari yang terdekat.');
    }

    public function clear(Request $request)
    {
        $request->session()->forget('foodsave.location');

        if ($request->boolean('clear_default') && $request->user()) {
            $request->user()->update(['lat' => null, 'lng' => null, 'location_label' => null]);
        }

        return back()->with('success', 'Lokasi dihapus.');
    }
}
