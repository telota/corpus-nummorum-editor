<?php

namespace App\Http\Controllers\dbi;

use Response;

class DataConverter {

    public function output ($entity, $id, $extension, $data) {

        if (strtolower($extension) !== 'jsonld') {
            $message = array_map(function ($key) use ($extension) {
                return '"'.$extension.'" '.$key;
            }, config('dbi.responses.api.not_supported'));

            return Response::json(['error' => $message], 404);
        }

        return Response::json([
            '@context' => $this->setContext($entity),
            '@graph' => $this->setGraph($entity, $data)
        ], 200);
    }

    public function setContext ($entity) {
        return [
            'dcterms'   => 'https://www.dublincore.org/specifications/dublin-core/dcmi-terms/terms/',
            'foaf'      => 'http://xmlns.com/foaf/spec/',
            'nmo'       => 'http://nomisma.org/ontology#',
            'skos'      => 'https://www.w3.org/2009/08/skos-reference/skos.html#',
            //'schema'    => 'https://schema.org/'
        ];
    }

    public function setGraph ($entity, $data) {

        $graph['@id'] = 'https://www.corpus-nummorum.eu/'.$entity.'/'.$data['id'];
        $graph['@type'] = $entity === 'coins' ? 'http://nomisma.org/ontology#NumismaticObject' : 'http://nomisma.org/ontology#TypeSeriesItem';

        // Name
        $graph['dcterms:title'][0]['@value'] = 'cn '.rtrim($entity, 's').' '.$data['id'];

        // META ------------------------------------------------------------------

        // Publisher
        $graph['dcterms:publisher'][0] = [
            '@id' => 'https://www.corpus-nummorum.eu',
            '@value' => 'Corpus Nummorum, Berlin-Brandenburg Academy of Sciences and Humanities, Jägerstraße 22/23, 10117 Berlin, Germany'
        ];

        // License
        $graph['dcterms:license'][0] = [
            '@id' => 'https://creativecommons.org/licenses/by/4.0',
            '@value' => 'CC BY 4.0',
            'skos:note' => 'This licence applies to the jsonld-text data only! The respective licence for other resources (e.g. linked images) must be requested from the stated rights holder.'
        ];

        // Source
        if (!empty($data['source']['link'])) $graph['dcterms:source'][0] = ['@id' => $data['source']['link']];

        // Created / Updated
        $graph['dcterms:created'][0] = ['@value' => $data['created_at']];
        $graph['dcterms:modified'][0] = ['@value' => $data['updated_at']];

        // DATA ------------------------------------------------------------------

        // Comment
        if (!empty($data['comment']['en'])) $graph['skos:note'][] = ['@value' => $data['comment']['en']];
        if (!empty($data['type_comment']['en'])) {
            if (empty($graph['skos:note']) || $graph['skos:note'][0]['@value'] !== $data['type_comment']['en']) {
                $graph['skos:note'][] = ['@value' => $data['type_comment']['en']];
            }
        }

        // Object Type
        $graph['nmo:'.($entity === 'types' ? 'represents' : 'has').'ObjectType'][0] = [
            '@id' => 'http://nomisma.org/id/coin',
            '@value' => 'coin'
        ];

        // Coin has Types
        if (!empty($data['types'])) {
            foreach ($data['types'] as $i => $t) {
                $ts[$i]['@id'] = 'https://www.corpus-nummorum.eu/types/'.$t['id'];
                //env('APP_URL').'/'.'cn_type_'.$t['id'].'.jsonld';
                $ts[$i]['dcterms:title'][0]['@value'] = 'cn type '.$t['id'];
            }
            if (!empty($ts)) $graph['nmo:hasTypeSeriesItem'] = $ts;
        }
        //nmo:hasTypeSeriesItem (for types)
        //nmo:TypeSeriesItem (for coins)

        // Mint
        if (!empty($data['mint']['link'])) $graph['nmo:hasMint'][0]['@id'] = $data['mint']['link'];
        if (!empty($data['mint']['id'])) $graph['nmo:hasMint'][0]['@value'] =
            (empty($data['mint']['text']['en']) ? $data['mint']['text']['de'] : $data['mint']['text']['en']).
            (empty($data['mint']['region']['text']) ? '' : (', '.(empty($data['mint']['region']['text']['en']) ? $data['mint']['region']['text']['de'] : $data['mint']['region']['text']['en'])));

        // Authority
        // Ruler
        if ($data['authority']['kind']['id'] === 1 && !empty($data['authority']['person']['name'])) {
            if (!empty($data['authority']['person']['link'])) $graph['nmo:hasAuthority'][0]['@id'] = $data['authority']['person']['link'];
            if (!empty($data['authority']['kind']['link'])) $graph['nmo:hasAuthority'][0]['@type'] = $data['authority']['kind']['link'];
            if (!empty($data['authority']['person']['name'])) $graph['nmo:hasAuthority'][0]['@value'] = $data['authority']['person']['name'];
        }
        // Tribe
        else if ($data['authority']['kind']['id'] === 2 && !empty($data['authority']['group']['name'])) {
            if (!empty($data['authority']['group']['link'])) $graph['nmo:hasAuthority'][0]['@id'] = $data['authority']['group']['link'];
            if (!empty($data['authority']['kind']['link'])) $graph['nmo:hasAuthority'][0]['@type'] = $data['authority']['kind']['link'];
            if (!empty($data['authority']['group']['name'])) $graph['nmo:hasAuthority'][0]['@value'] = $data['authority']['group']['name'];
        }
        // stated Ruler
        else if ($data['authority']['kind']['id'] !== 1 && !empty($data['authority']['person']['name'])) {
            if (!empty($data['authority']['person']['link'])) $graph['hasStatedAuthority'][0]['@id'] = $data['authority']['person']['link'];
            if (!empty($data['authority']['person']['name'])) $graph['hasStatedAuthority'][0]['@value'] = $data['authority']['person']['name'];
        }
        // stated Tribe
        else if ($data['authority']['kind']['id'] !== 2 && !empty($data['authority']['group']['name'])) {
            if (!empty($data['authority']['group']['link'])) $graph['hasStatedAuthority'][0]['@id'] = $data['authority']['group']['link'];
            if (!empty($data['authority']['group']['name'])) $graph['hasStatedAuthority'][0]['@value'] = $data['authority']['group']['name'];
        }

        // Issuer
        if (!empty($data['issuer']['link'])) $graph['nmo:hasIssuer'][0] = ['@id' => $data['issuer']['link']];
        if (!empty($data['issuer']['name'])) $graph['nmo:hasIssuer'][0] = ['@value' => $data['issuer']['name']];

        // Date
        if (!empty($data['date']['text']['en']) || !empty($data['date']['text']['de'])) {
            $graph['nmo:hasDate']['nmo:hasProductionDate'][0] = ['@value' =>
                !empty($data['date']['text']['en']) ? $data['date']['text']['en'] : str_replace('n. Chr.', 'AD', str_replace('v. Chr.', 'BC', $data['date']['text']['de']))
            ];
        }
        // Period??

        // Diameter
        if (!empty($data['diameter']['value_max'])) $graph['nmo:hasDiameter']['nmo:hasMaxDiameter'][0] = [
            '@value' => $data['diameter']['value_max'],
            'unit' => 'mm'
        ];
        if (!empty($data['diameter']['value_min'])) $graph['nmo:hasDiameter']['nmo:hasMinDiameter'][0] = [
            '@value' => $data['diameter']['value_min'],
            'unit' => 'mm'
        ];

        // Weight
        if (!empty($data['weight']['value'])) $graph['nmo:hasWeight'][0] = [
            '@value' => $data['weight']['value'],
            'unit' => 'g'
        ];

        // Material
        if (!empty($data['material']['link'])) $graph['nmo:hasMaterial'][0]['@id'] = $data['material']['link'];
        if (!empty($data['material']['text']['en'])) $graph['nmo:hasMaterial'][0]['@value'] = $data['material']['text']['en'];
        else if (!empty($data['material']['text']['de'])) $graph['hasMaterial'][0]['@value'] = $data['material']['text']['de'];

        // Denominaton
        if (!empty($data['denomination']['link'])) $graph['nmo:hasDenomination'][0]['@id'] = $data['denomination']['link'];
        if (!empty($data['denomination']['text']['en'])) $graph['nmo:hasDenomination'][0]['@value'] = $data['denomination']['text']['en'];
        else if (!empty($data['denomination']['text']['de'])) $graph['nmo:hasDenomination'][0]['@value'] = $data['denomination']['text']['de'];

        // Standard
        if (!empty($data['standard']['link'])) $graph['nmo:hasWeightStandard'][0]['@id'] = $data['standard']['link'];
        if (!empty($data['standard']['text']['en'])) $graph['nmo:hasWeightStandard'][0]['@value'] = $data['standard']['text']['en'];
        else if (!empty($data['standard']['text']['de'])) $graph['nmo:hasWeightStandard'][0]['@value'] = $data['standard']['text']['de'];

        //nmo:hasManufacture (struck or cast)
        //nmo:hasProductionObject
            //nmo:hasDie

        // Axis
        if (!empty($data['axis']) || !empty($data['axes'])) {
            foreach (empty($data['axes']) ? [$data['axis']] : $data['axes'] as $i => $a) {
                $graph['nmo:hasAxis'][$i]['@value'] = is_array($a) ? $a['value'] : $a;
            }
        }

        // Appearance
        foreach(['obverse', 'reverse'] as $side) {
            $gs = null;
            if (!empty($data[$side]['design']['text']['en'])) $gs['dcterms:description'][0] = ['@value' => $data[$side]['design']['text']['en']];
            if (!empty($data[$side]['legend']['string'])) $gs['nmo:hasLegend'][0] = ['@value' => $data[$side]['legend']['string']];

            if (!empty($gs)) $graph['nmo:hasAppearance']['nmo:hasFace']['nmo:has'.ucfirst($side)] = $gs;
        }
        // Persons
        if (!empty($data['persons'])) {
            $i = 0;
            foreach ($data['persons'] as $p) {
                if ($p['function']['id'] === 4) {
                    if (!empty($p['link'])) $graph['nmo:hasAppearance']['nmo:hasIconography']['nmo:hasPortrait'][$i]['@id'] = $p['link'];
                    if (!empty($p['name'])) $graph['nmo:hasAppearance']['nmo:hasIconography']['nmo:hasPortrait'][$i]['@value'] = $p['name'];
                    ++$i;
                }
            }
        }

        //nmo:hasAppearance
            //nmo:hasFace
                //nmo:hasObverse
                    //nmo:hasControlmark
                    //nmo:hasLegend
                //nmo:hasReverse
                    //nmo:hasControlmark
                    //nmo:hasLegend
            //nmo:hasIconography
                //nmo:hasPortrait
                //nmo:hasCountermark

        // Pecularities
        if (!empty($data['pecularities']['en'])) $graph['nmo:Peculiarity'][0] = ['@value' => $data['pecularities']['en']];


        // Context - Findspot
        if (!empty($data['findspot']) || !empty($data['findspots'])) {
            foreach (empty($data['findspots']) ? [$data['findspot']] : $data['findspots'] as $i => $fs) {
                if (!empty($fs['link'])) $graph['nmo:hasContext']['nmo:hasFindspot'][$i]['@id'] = $fs['link'];
                if (!empty($fs['name'])) $graph['nmo:hasContext']['nmo:hasFindspot'][$i]['@value'] = $fs['name'].($fs['country'] ? (' ('.strtoupper($fs['country']).')') : '');
            }
        }
        // Context - Hoards
        if (!empty($data['hoard']) || !empty($data['hoards'])) {
            foreach (empty($data['hoards']) ? [$data['hoard']] : $data['hoards'] as $i => $hs) {
                if (!empty($hs['link'])) $graph['nmo:hasContext']['nmo:Find']['nmo:Hoard'][$i]['@id'] = $hs['link'];
                if (!empty($hs['name'])) $graph['nmo:hasContext']['nmo:Find']['nmo:Hoard'][$i]['@value'] = $hs['name'];
            }
        }

        // Owner
        if (!empty($data['owner']['original']['link'])) $graph['nmo:hasCollection'][0]['@id'] = $data['owner']['original']['link'];
        if (!empty($data['owner']['original']['name'])) $graph['nmo:hasCollection'][0]['@value'] = $data['owner']['original']['name'].
        ($data['owner']['original']['city'] ? (', '.$data['owner']['original']['city']) : '').
        ($data['owner']['original']['country'] ? (' ('.strtoupper($data['owner']['original']['country']).')') : '').
        ($data['owner']['original']['collection_nr'] ? (', collection nr. '.$data['owner']['original']['collection_nr']) : '');

        // References
        $gr = [];
        foreach (['citations', 'literature', 'type_literature'] as $references) {
            if (!empty($data[$references])) {
                foreach ($data[$references] as $refs) {
                    if (!isset($gr[$refs['id']])) {
                        $gr[$refs['id']] = [
                            '@id' => $refs['link'],
                            '@type' => 'https://www.dublincore.org/specifications/dublin-core/dcmi-terms/terms/BibliographicResource',
                            '@value' => $refs['title'].(empty($refs['quote']['text']['en']) ? '' : (', '.$refs['quote']['text']['en'])),
                        ];
                        if (!empty($refs['quote']['comment']['en'])) $gr[$refs['id']]['schema:Comment'] = $refs['quote']['comment']['en'];
                    }
                }
            }
        }
        if (!empty($gr)) $graph['nmo:hasReferenceWork'] = array_values($gr);

        // Images
        if (!empty($data['images'])) {
            $i = 0;
            foreach ($data['images'] as $img) {
                foreach (['obverse', 'reverse'] as $side) {
                    $link = $img[$side]['link'];
                    if (!empty($link)) {
                        if (substr($link, 0, 4) !== 'http') {
                            $link = env('APP_URL').'/storage/'.ltrim(ltrim($link, '/'), 'storage/');
                        }

                        $imgs[$i]['@id'] = $link;

                        if (substr($link, 0, strlen(env('APP_URL'))) === env('APP_URL')) {
                            $file = substr($link, strlen(env('APP_URL')) + 9);
                            $split = explode('.', $file);
                            if (in_array(strtolower($split[1]), ['tif', 'tiff'])) {
                                $imgs[$i]['foaf:thumbnail'] = config('dbi.url.digilib_scaler').$file.'&dw=100&dh=100';
                            }
                        }

                        $img_type = $img[$side]['kind'] === 'original' ? 'original' : 'reproduction';

                        $imgs[$i]['skos:note'] = $img_type.', '.$side;

                        if (!empty($img[$side]['photographer']) && !in_array($img[$side]['photographer'], ['privat', 'unbekannt'])) $imgs[$i]['dcterms:creator'][0]['@value'] = $img[$side]['photographer'];
                        else if (!empty($data['owner'][$img_type]['name'])) $imgs[$i]['dcterms:creator'][0]['@value'] = $data['owner'][$img_type]['name'];

                        if (!empty($data['owner'][$img_type]['link'])) $imgs[$i]['dcterms:rightsHolder'][0]['@id'] = $data['owner'][$img_type]['link'];
                        if (!empty($data['owner'][$img_type]['name'])) $imgs[$i]['dcterms:rightsHolder'][0]['@value'] = $data['owner'][$img_type]['name'].
                        ($data['owner'][$img_type]['city'] ? (', '.$data['owner'][$img_type]['city']) : '').
                        ($data['owner'][$img_type]['country'] ? (' ('.strtoupper($data['owner'][$img_type]['country']).')') : '');

                        ++$i;
                    }
                }
            }
        }
        if (!empty($imgs)) $graph['dcterms:Image'] = $imgs;

        // ----------
        return $graph;
    }
}
