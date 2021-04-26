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
            'dcterms'   => 'https://dublincore.org/specifications/dublin-core/dcmi-terms/',
            'foaf'      => 'http://xmlns.com/foaf/spec/',
            'nmo'       => 'http://nomisma.org/ontology#',
            'skos'      => 'https://www.w3.org/2009/08/skos-reference/skos.html#',
            'schema'    => 'https://schema.org/'
        ];
    }

    public function setGraph ($entity, $data) {

        $graph['@id'] = 'https://www.corpus-nummorum.eu/'.$entity.'/'.$data['id'];
        $graph['@type'] = $entity === 'coins' ? 'http://nomisma.org/ontology#NumismaticObject' : 'http://nomisma.org/ontology#TypeSeriesItem';

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
            'skos:note' => 'This only applies to the text data. The licence for other data types (e.g. images) is declared separately.'
        ];

        // Source
        if (!empty($data['source']['link'])) $graph['dcterms:source'][0] = ['@id' => $data['source']['link']];

        // Created / Updated
        $graph['dcterms:created'][0] = ['@value' => $data['created_at']];
        $graph['dcterms:modified'][0] = ['@value' => $data['updated_at']];

        // DATA ------------------------------------------------------------------

        // Name
        $graph['dcterms:title'][0] = ['@value' => 'cn '.rtrim($entity, 's').' '.$data['id']];

        // Object Type
        $graph['nmo:'.($entity === 'types' ? 'represents' : 'has').'ObjectType'][0] = [
            '@id' => 'http://nomisma.org/id/coin',
            '@value' => 'coin'
        ];

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

        // Material
        if (!empty($data['material']['link'])) $graph['nmo:hasMaterial'][0] = [
            '@id' => $data['material']['link'],
            '@value' => empty($data['material']['text']['en']) ? $data['material']['text']['de'] : $data['material']['text']['en']
        ];

        // Denominaton
        if (!empty($data['denomination']['link'])) $graph['nmo:hasDenomination'][0] = [
            '@id' => $data['denomination']['link'],
            '@value' => empty($data['denomination']['text']['en']) ? $data['denomination']['text']['de'] : $data['denomination']['text']['en']
        ];

        // Weight
        if (!empty($data['weight']['value'])) $graph['nmo:hasWeight'][0] = [
            '@value' => $data['weight']['value'],
            'unit' => 'g'
        ];

        // Diameter
        if (!empty($data['diameter']['value_max'])) $graph['nmo:hasDiameter']['nmo:hasMaxDiameter'][0] = [
            '@value' => $data['diameter']['value_max'],
            'unit' => 'mm'
        ];
        if (!empty($data['diameter']['value_min'])) $graph['nmo:hasDiameter']['nmo:hasMinDiameter'][0] = [
            '@value' => $data['diameter']['value_min'],
            'unit' => 'mm'
        ];

        //nmo:hasManufacture (struck or cast)
        //nmo:hasProductionObject
            //nmo:hasDie

        // Axis
        if (!empty($data['axis'])) $graph['nmo:hasAxis'][0] = ['@value' => $data['axis']];

        //dcterms:Standard
            //nmo:WeightStandard

        // Appearance
        foreach(['obverse', 'reverse'] as $side) {
            if (!empty($data[$side]['design']['text']['en'])) $gs['foaf:depiction'][0] = ['@value' => $data[$side]['design']['text']['en']];
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

        // Images
        //Tdcterms:rightsHolder

        // Pecularities
        if (!empty($data['pecularities']['en'])) $graph['nmo:Peculiarity'][0] = ['@value' => $data['pecularities']['en']];

        //schema:Comment

        //nmo:hasContext
            //nmo:hasFindspot
            //nmo:Find
                //nmo:Hoard

        //nmo:hasReferenceWork (A published work of reference relevant to a numismatic object.)
            //dcterms:BibliographicResource
            //nmo:TypeSeries



        //nmo:hasCollection

        //nmo:Controlmark ?
        //nmo:Cauntermark ?
        //nmo:Ethnic (tribe) ?

        // ----------
        return $graph;
    }
}
