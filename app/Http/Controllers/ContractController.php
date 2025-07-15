<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\ContractFields;
use App\Models\ContractValues;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;

class ContractController extends Controller
{

    public function index()
    {
        $contracts = Contract::paginate(10);
        return view('contracts.index', compact('contracts'));
    }


    public function create(string $type)
    {
        $fields = ContractFields::where('contract_type', $type)->get();

        return view('contracts.create', compact('fields', 'type'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_type' => 'required|string',
            'fields' => 'required|array',
            'signature_client' => 'nullable|string',
            'signature_agent' => 'nullable|string',
        ]);

        $contract = Contract::create([
            'contract_type' => $request->contract_type,
            'agent_id' => auth()->id(),
            'client_signed' => $request->signature_client == null ? false : true,
            'agent_signed' => $request->signature_agent == null ? false : true,
            'signature_client' => $request->signature_client,
            'signature_agent' => $request->signature_agent,
        ]);

        foreach ($request->fields as $fieldId => $value) {
            ContractValues::create([
                'contract_id' => $contract->id,
                'contract_field_id' => $fieldId,
                'value' => $value,
            ]);
        }

        return redirect()->route('contracts.show', $contract);
    }

    public function show(Contract $contract)
    {
        $contract->load('values.field');

        $fields = $contract->values->mapWithKeys(fn($v) => [$v->field->name => $v->value])->toArray();

        $this->exportPdf($contract);

        return view("contracts.templates.{$contract->contract_type->value}", compact('contract', 'fields'));
    }

    public function exportPdf(Contract $contract)
    {
        $fields = $contract->values->mapWithKeys(fn($v) => [$v->field->name => $v->value])->toArray();
        $pdf = Pdf::loadView("contracts.templates.{$contract->contract_type->value}", compact('contract', 'fields'));
        return $pdf->download("contract_{$contract->id}.pdf");
    }
}
