<?php
namespace App\Http\Controllers\XmlImporter;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCitationRequest;
use Illuminate\Http\Request;
use Validator;
use XmlParser;
use Storage;

class XmlImporterController extends Controller
{
    /**
     * To validate file
     * 
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function validateXml(Request $request): bool
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required'
        ]);
        
        if($validator->fails() || strtolower($request->file->getClientOriginalExtension()) != 'xml') {
            return true;
        }

        return false;
    }

    public function run(Request $request)
    {
        $validatedInput = $this->validateXml($request);
        if($validatedInput) {
            return response()->json([
                'message' => "Whoops!! seems like an invalid file. Uploaded file should be XML."
            ], 555);
        }
        $data = $request->file('file')->store('imports', 'local');
     
        // $file = Storage::disk('local')->getDriver()->getAdapter()->applyPathPrefix('imports/Fpa99g9lEIPAF6tbXgR8EKIOp6m90sLJtfaGeWbB.xml');
        // $xml = XmlParser::load($file);
        // dump($xml);
        // die;   
    }
}