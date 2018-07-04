<?php

namespace App\Http\Controllers\XmlImporter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use XmlParser;
use Storage;
use DB;
use App\EventType;
use App\Repository;
use LaravelEnso\AddressesManager\app\Models\Address;
use LaravelEnso\AddressesManager\app\Models\Country;
use App\Note;
use App\Source;
use App\Citation;
use App\Event;

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
            'file' => 'required',
        ]);

        if ($validator->fails() || strtolower($request->file->getClientOriginalExtension()) != 'xml') {
            return true;
        }

        return false;
    }

    /**
     * To Handle XML Import
     *
     * @param \Illuminate\Http\Request $request
     * @return json
     */
    public function run(Request $request)
    {
        $validatedInput = $this->validateXml($request);
        if ($validatedInput) {
            return response()->json([
                'message' => 'Whoops!! seems like an invalid file. Uploaded file should be XML.',
            ], 555);
        }

        $fileName = $request->file('file')->store('imports', 'local');

        return $this->importXML($fileName);
    }

    /**
     * First Import Notes
     * Second Import Repository
     * Third Import Source
     * Fourth Import Citations
     * Fifth will be Places ( Problem is Place object only contains the name of city, How do we add this as a address)
     * Sixth will be Tags ( Which table we will use for this?)
     * Eight Will be Events (In event there is id of place how to adjust it)
     * Nine will be families (There are no Key for Individual, and they are giving child reference and citation ref)
     */
    public function importXML($fileName='imports/Fpa99g9lEIPAF6tbXgR8EKIOp6m90sLJtfaGeWbB.xml')
    {
        $file          = Storage::disk('local')->getDriver()->getAdapter()->applyPathPrefix($fileName);
        $xml           = XmlParser::load($file);
        $collection    = collect($xml->getContent());
        // $notes         = $this->importNotes($collection['notes']);
        // $repositories  = $this->importRepositories($collection['repositories']);
        // $sources  = $this->importSources($collection['sources']);
        // $citations  = $this->importCitations($collection['citations']);
        // $places  = $this->importPlaces($collection['places']);
        // $tags  = $this->importTags($collection['tags']);
        // $events  = $this->importEvents($collection['events']);
        
        // dd($collection['places']);
    }

    /**
     * To import Events
     *
     * @param SimpleXMLElement $tags
     * @return array
     */
    public function importEvents($events)
    {
        $_events  = [];

        DB::transaction(function () use ($events, &$_events) {
            foreach ($events as $key => $value) {
                $value = (array)$value;
                if (isset($value['@attributes'])) {
                    $attributes                        = $value['@attributes'];
                    $event = Event::firstOrCreate([
                        'hlink' => $attributes['handle'],
                        'event_type' => 'App\Family',
                        'event_id' => 0,
                        'is_active' => 1,
                    ]);
                    // $event->
                    // $_events[] = $event;
                }
            }
        });

        return $_events;
    }

    /**
     * To import Tags
     *
     * @param SimpleXMLElement $tags
     * @return array
     */
    public function importTags($tags)
    {
        $_tags  = [];

        DB::transaction(function () use ($tags, &$_tags) {
            foreach ($tags as $key => $value) {
                $value = (array)$value;
                if (isset($value['@attributes'])) {
                    $attributes                        = $value['@attributes'];
                    $note = Note::firstOrCreate(['hlink' => $attributes['handle']]);
                    $note->name          = $attributes['name'];
                    $note->description   = 'Tag';
                    $note->is_active     = 1;
                    $note->save();
                    $_tags[] = $note;
                }
            }
        });

        return $_tags;
    }

    /**
     * To import Placees Object
     *
     * @param SimpleXMLElement $places
     * @return array
     */
    public function importPlaces($places)
    {
        $_places = $this->getSplitedPlaces($places);

        DB::transaction(function () use ($_places) {
            foreach ($_places['city'] as $key => $value) {
                if (!isset($value['pname']) || !isset($value['placeref'])) {
                    continue;
                }

                $attributes = $value['@attributes'];
                $placeref   = reset($value['placeref'])['hlink'];
                $country    = $this->getCounty($_places, $placeref);
                $pname      = reset($value['pname'])['value'];

                $address = Address::firstOrCreate([
                    'addressable_type' => 'App\Places',
                    'addressable_id'   => 0,
                    'hlink'            => $placeref,
                    'city'             => $pname,
                    'country_id'       => $country,
                ]);

                if (isset($value['coord'])) {
                    $address->lat = reset($value['coord'])['lat'];
                    $address->long = reset($value['coord'])['long'];
                }

                $address->is_default = 0;
                $address->save();
            }
        });

        return $_places;
    }

    /**
     * Method will differenciate records into Country, State, City, County
     *
     * @param SimpleXMLElement $places
     * @return $array
     */
    public function getSplitedPlaces($places)
    {
        $country = $state = $county = $city = [];
        foreach ($places as $key => $value) {
            $value = (array)$value;
            if (isset($value['@attributes'])) {
                $attributes = $value['@attributes'];

                switch (strtolower($attributes['type'])) {
                    case 'city':
                        $city[$attributes['handle']] = $value;
                        break;
                    case 'parish':
                    case 'county':
                        $county[$attributes['handle']] = $value;
                        break;
                    case 'state':
                        $state[$attributes['handle']] = $value;
                        break;
                    case 'country':
                        $country[$attributes['handle']] = $value;
                        break;
                    default:
                }
            }
        }

        return [
            'country' => $country,
            'city'    => $city,
            'state'   => $state,
            'county'  => $county,
        ];
    }

    /**
     * To County from hLink reference.
     * If found then find state from it.
     *
     * @param array $places
     * @param SimpleXMLElement $placeref
     * @return array
     */
    public function getCounty($places, $placeref)
    {
        if (isset($places['county'][$placeref])) {
            $placeref = reset($places['county'][$placeref]);

            return $this->getState($places, $placeref['handle']);
        }

        return $this->getState($places, $placeref);
    }

    /**
     * To get Sounty from hLink reference.
     * If found then find country from it.
     *
     * @param array $places
     * @param SimpleXMLElement $placeref
     * @return array
     */
    public function getState($places, $placeref)
    {
        if (isset($places['state'][$placeref])) {
            $placeref = reset($places['state'][$placeref]);

            return $this->getCountry($places, $placeref['handle']);
        }

        return $this->getCountry($places, $placeref);
    }

    /**
     * To get Country from hLink reference.
     *
     * @param array $places
     * @param SimpleXMLElement $placeref
     * @return \App\Country
     */
    public function getCountry($places, $placeref)
    {
        if (isset($places['country'][$placeref])) {
            $placeref = $places['country'][$placeref];
            $country  = reset($placeref['pname']);

            return optional(Country::where('name', $country['value'])->orWhere('iso_3166_3', $country['value'])->first())->id;
        }

        return optional(Country::orWhere('iso_3166_3', 'USA')->first())->id;
    }

    /**
     * To import Citations
     *
     * @param SimpleXMLElement $citations
     * @return array
     */
    public function importCitations($citations)
    {
        $_citations  = [];
        DB::transaction(function () use ($citations, &$_citations) {
            foreach ($citations as $key => $value) {
                $value = (array)$value;
                if (isset($value['@attributes'])) {
                    $attributes = $value['@attributes'];
                    $citation = Citation::firstOrCreate([
                        'hlink' => $attributes['handle'],
                    ]);

                    $citation->name = $attributes['id'];
                    $citation->date	= isset($value['dateval']) ? $value['dateval']['@attributes']['val'] : null;
                    $citation->is_active = 1;
                    $citation->confidence = $value['confidence'];

                    if (isset($value['sourceref'])) {
                        $citation->source_id = optional(Source::where('hlink', $value['sourceref']['@attributes']['hlink'])->first())->id;
                    }

                    $citation->save();
                }
            }
        });

        return $_citations;
    }

    /**
     * To import sources
     *
     * @param SimpleXMLElement $sources
     * @return array
     */
    public function importSources($sources)
    {
        $_sources  = [];
        DB::transaction(function () use ($sources, &$_sources) {
            foreach ($sources as $key => $value) {
                $value = (array)$value;
                if (isset($value['@attributes'])) {
                    $attributes = $value['@attributes'];
                    $source = Source::firstOrCreate([
                        'hlink' => $attributes['handle'],
                    ]);

                    $source->name = $value['stitle'];
                    $source->save();

                    if (isset($value['reporef'])) {
                        $this->attachSourceRepository($source, $value['reporef']);
                    }

                    if (isset($value['noteref'])) {
                        $this->attachSourceNote($source, $value['noteref']);
                    }
                }
            }
        });

        return $_sources;
    }

    /**
     * To import Notes
     *
     * @param SimpleXMLElement $notes
     * @return array
     */
    public function importNotes($notes)
    {
        $_notes  = [];

        DB::transaction(function () use ($notes, &$_notes) {
            foreach ($notes as $key => $value) {
                $value = (array)$value;
                if (isset($value['@attributes'])) {
                    $attributes                        = $value['@attributes'];
                    $note = Note::firstOrCreate(['hlink' => $attributes['handle']]);
                    $note->name          = $attributes['type'];
                    $note->description   = $value['text'];
                    $note->is_active     = 1;
                    $note->save();
                    $_notes[] = $note;
                }
            }
        });

        return $_notes;
    }

    /**
     * To Import Repositories
     *
     * @param SimpleXMLElement $repositories
     * @return array
     */
    public function importRepositories($repositories)
    {
        $_repositories  = [];

        DB::transaction(function () use ($repositories, &$_repositories) {
            foreach ($repositories as $key => $value) {
                $value = (array)$value;
                if (isset($value['@attributes'])) {
                    $attributes             = $value['@attributes'];
                    $repository             = Repository::firstOrCreate(['hlink'=>$attributes['handle']]);
                    $repository->name       = $value['rname'];
                    $repository->is_active  = 1;
                    $repository->type_id    = $this->addEventType($value['type'])->id;
                    $repository->description= '';
                    $repository->save();

                    $this->addAddress('App\Repository', $repository->id, $value['address']);
                    $_repositories[] = $repository;
                }
            }
        });

        // Skiped Index citationref and noteref and url
        return $_repositories;
    }

    /**
     * Method used to add EventType based on Name.
     * If doesn't exists on database then it will create
     *
     * @param string $name
     * @return \App\EventType $entity
     */
    public function addEventType($name)
    {
        $entity = EventType::firstOrCreate([
            'name' => $name,
        ]);

        $entity->is_active = 1;
        $entity->save();

        return $entity;
    }

    /**
     * To Store Address
     *
     * @param string $type
     * @param INT $type_id
     * @param SimpleXMLElement $data
     *
     * @return \LaravelEnso\AddressesManager\app\Models\Address
     */
    public function addAddress($type, $type_id, $data)
    {
        $data                      = (array)$data;
        $counter_id                = Country::where('iso_3166_3', $data['country'])->first();
        $address                   = Address::where([
            'addressable_type' => $type,
            'addressable_id'   => $type_id,
            'country_id'       => $counter_id,
            'street'           => $data['street'],
        ])
        ->first();

        if (!empty($address)) {
            return $address;
        }

        $address                   = new Address;
        $address->addressable_type = $type;
        $address->addressable_id   = $type_id;
        $address->country_id       = optional(Country::where('iso_3166_3', $data['country'])->first())->id;
        $address->city             = $data['city'];
        $address->street           = $data['street'];
        $address->street_type      = 'Road';
        $address->postal_area      = @$data['postal'];
        $address->save();

        return $address;
    }

    /**
     * Attach Source Repository
     *
     * @param \App\Source $source
     * @param array $data
     * @return \App\Source
     */
    public function attachSourceRepository(Source $source, $data)
    {
        if (is_array($data)) {
            $hlinks = array_map(function ($value) {
                return $value['hlink'];
            }, $data);
        } else {
            $hlinks[] = $data['hlink'];
        }

        $repos = Repository::whereIn('hlink', array_map('reset', $hlinks))->get()->pluck('id');
        $source->repositories()->sync($repos);

        return $source;
    }

    /**
     * Attach Source Note
     *
     * @param \App\Source $source
     * @param array $data
     * @return \App\Source
     */
    public function attachSourceNote(Source $source, $data)
    {
        if (is_array($data)) {
            $hlinks = array_map(function ($value) {
                return $value['hlink'];
            }, $data);
        } else {
            $hlinks[] = $data['hlink'];
        }

        $notes = Note::where('hlink', array_map('reset', $hlinks))->get()->pluck('id');
        $source->notes()->sync($notes);

        return $notes;
    }
}
