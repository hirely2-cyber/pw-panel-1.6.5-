<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PW\GameVersion;
use App\Services\PW\PwSocketException;
use App\Services\PW\Roles\GRoleReader;
use App\Services\PW\Roles\RoleService;
use App\Services\PW\Server;
use App\Services\PW\Socket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CharacterController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize_('character.view');

        $filters = [
            'userid'    => trim((string) $request->input('userid', '')),
            'username'  => trim((string) $request->input('username', '')),
            'role_id'   => trim((string) $request->input('role_id', '')),
            'role_name' => trim((string) $request->input('role_name', '')),
        ];

        $query = DB::connection('pw_game')->table('roles as r')
            ->leftJoin('users as u', 'u.ID', '=', 'r.account_id')
            ->select(
                'r.role_id as id',
                'r.role_name as name',
                'r.account_id as userid',
                'u.name as username',
                'r.role_race as race',
                'r.role_occupation as cls',
                'r.role_gender as gender',
                'r.role_level as level',
                'r.faction_name as faction_name',
                'r.faction_level as faction_level'
            );

        if ($filters['userid']    !== '') $query->where('r.account_id', $filters['userid']);
        if ($filters['username']  !== '') $query->where('u.name', 'like', "%{$filters['username']}%");
        if ($filters['role_id']   !== '') $query->where('r.role_id', $filters['role_id']);
        if ($filters['role_name'] !== '') $query->where('r.role_name', 'like', "%{$filters['role_name']}%");

        $characters = $query->orderByDesc('r.role_id')->paginate(25)->withQueryString();

        return view('panel.character.index', compact('characters', 'filters'));
    }

    public function show(string $id, RoleService $roles, \App\Services\PW\CashService $cash)
    {
        $this->authorize_('character.view');

        $char = DB::connection('pw_game')->table('roles as r')
            ->leftJoin('users as u', 'u.ID', '=', 'r.account_id')
            ->where('r.role_id', $id)
            ->select('r.*', 'r.role_id as id', 'r.role_name as name', 'r.account_id as userid', 'u.name as username')
            ->first();

        abort_unless($char, 404);

        $version   = GameVersion::detect();
        $role      = $roles->fetch((int) $id, $version);
        $summary   = $role ? $roles->summary($role) : null;
        $cubi      = $cash->forAccount((int) $char->userid);

        return view('panel.character.show', compact('char', 'version', 'summary', 'cubi'));
    }

    public function update(Request $request, string $id, RoleService $roles): \Illuminate\Http\RedirectResponse
    {
        $this->authorize_('character.edit');

        $data = $request->validate([
            'world_tag'    => 'required|integer|min:0',
            'pos_x'        => 'required|numeric',
            'pos_y'        => 'required|numeric',
            'pos_z'        => 'required|numeric',
            'reputation'   => 'required|integer|min:0',
            'exp'          => 'required|integer|min:0',
            'sp'           => 'required|integer|min:0',
            'level2'       => 'required|integer|min:0',
            'max_ap'       => 'required|integer|min:0',
            'pocket_money' => 'required|integer|min:0',
            'store_money'  => 'required|integer|min:0',
        ]);

        $char = DB::connection('pw_game')->table('roles')
            ->where('role_id', $id)
            ->first();
        abort_unless($char, 404);

        $version = GameVersion::detect();
        $role = $roles->fetch((int) $id, $version);

        if (!$role) {
            return back()->with('error', 'Could not fetch character data from game server.');
        }

        // Apply editable fields to the raw ['type'=>..., 'value'=>...] node tree
        $role['role']['status']['worldtag']['value']           = (int)   $data['world_tag'];
        $role['role']['status']['posx']['value']               = (float) $data['pos_x'];
        $role['role']['status']['posy']['value']               = (float) $data['pos_y'];
        $role['role']['status']['posz']['value']               = (float) $data['pos_z'];
        $role['role']['status']['reputation']['value']         = (int)   $data['reputation'];
        $role['role']['status']['exp']['value']                = (int)   $data['exp'];
        $role['role']['status']['sp']['value']                 = (int)   $data['sp'];
        $role['role']['status']['level2']['value']             = (int)   $data['level2'];
        $role['role']['status']['property']['max_ap']['value'] = (int)   $data['max_ap'];
        $role['role']['pocket']['money']['value']              = (int)   $data['pocket_money'];
        $role['role']['storehouse']['money']['value']          = (int)   $data['store_money'];

        $srv = Server::current();
        GRoleReader::setStruct(RoleService::resolveStructClass($version));
        GRoleReader::$cycle = false;
        $sock = Socket::fromCurrent();

        try {
            $ok = GRoleReader::writeCharacter($sock, (int) $srv->db_port, (int) $id, $role, false);
        } catch (PwSocketException $e) {
            return back()->with('error', 'Socket error: ' . $e->getMessage());
        }

        if (!$ok) {
            return back()->with('error', 'Server returned server:0 — character not saved.');
        }

        return back()->with('success', 'Character saved successfully.');
    }

    public function rawXml(string $id, RoleService $roles)
    {
        $this->authorize_('character.view');

        $char = DB::connection('pw_game')->table('roles as r')
            ->leftJoin('users as u', 'u.ID', '=', 'r.account_id')
            ->where('r.role_id', $id)
            ->select('r.role_id as id', 'r.role_name as name', 'r.account_id as userid', 'u.name as username')
            ->first();

        abort_unless($char, 404);

        $version = GameVersion::detect();
        $struct  = RoleService::resolveStructClass($version);
        GRoleReader::setStruct($struct);
        GRoleReader::$cycle = false;

        $srv  = Server::current();
        $sock = Socket::fromCurrent();

        try {
            $raw = GRoleReader::readCharacter($sock, (int) $srv->db_port, (int) $id, true);
        } catch (PwSocketException $e) {
            abort(500, 'Socket error: ' . $e->getMessage());
        }

        if (empty($raw['role'])) {
            abort(502, 'Could not fetch character data from game server.');
        }

        // Build XML string like iweb xml::encode()
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $root = $doc->createElement('role');
        $doc->appendChild($root);
        self::buildXmlDom($doc, $root, $raw['role']);
        $xmlString = $doc->saveXML();

        return view('panel.character.xml', compact('char', 'xmlString', 'version'));
    }

    /**
     * Recursively build DOMDocument from decoded role tree.
     * Leaf nodes: ['type'=>..., 'value'=>...] → <key type="T" value="V"/>
     * Branch nodes → <key>...children...</key>
     */
    private static function buildXmlDom(\DOMDocument $doc, \DOMElement $parent, array $node): void
    {
        foreach ($node as $key => $value) {
            $tag = is_numeric($key) ? 'item' . $key : preg_replace('/[^a-zA-Z0-9_\-.]/', '_', (string) $key);
            if (!is_array($value)) continue;

            if (isset($value['type']) && array_key_exists('value', $value)) {
                // Leaf node
                $val = $value['value'];
                if (is_string($val) && strlen($val) > 0 && !mb_check_encoding($val, 'UTF-8')) {
                    $val = bin2hex($val);
                } elseif (is_bool($val)) {
                    $val = $val ? '1' : '0';
                }
                $el = $doc->createElement($tag);
                $el->setAttribute('type', (string) $value['type']);
                $el->setAttribute('value', (string) $val);
                $parent->appendChild($el);
            } else {
                // Branch node
                $el = $doc->createElement($tag);
                $parent->appendChild($el);
                self::buildXmlDom($doc, $el, $value);
            }
        }
    }

    /**
     * POST /characters/{id}/xml — save edited XML back to game server.
     */
    public function saveXml(Request $request, string $id)
    {
        $this->authorize_('character.edit');

        $data = $request->validate([
            'xml' => ['required', 'string', 'max:2097152'],
        ]);

        // Parse submitted XML
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (! $doc->loadXML($data['xml'])) {
            $err = libxml_get_errors();
            libxml_clear_errors();
            $msg = $err ? $err[0]->message : 'parse error';
            return back()->with('error', 'XML tidak valid: ' . trim($msg));
        }
        libxml_clear_errors();

        $root = $doc->documentElement;
        if (! $root || $root->tagName !== 'role') {
            return back()->with('error', 'Root element harus <role>.');
        }

        $roleArray = ['role' => self::parseXmlToRole($root)];

        $version = GameVersion::detect();
        GRoleReader::setStruct(RoleService::resolveStructClass($version));
        GRoleReader::$cycle = false;

        $srv  = Server::current();
        $sock = Socket::fromCurrent();

        try {
            // octets values in XML are hex strings → octetToData=true converts hex→binary
            $ok = GRoleReader::writeCharacter($sock, (int) $srv->db_port, (int) $id, $roleArray, true);
        } catch (PwSocketException $e) {
            return back()->with('error', 'Socket error: ' . $e->getMessage());
        }

        if (! $ok) {
            return back()->with('error', 'Server returned server:0 — character tidak tersimpan.');
        }

        return back()->with('ok', 'XML karakter berhasil disimpan.');
    }

    /**
     * Recursively parse DOMElement children → role array.
     * Reverse of buildXmlDom: leaf = has type+value attrs, branch = has children.
     * Octets values stay as hex strings (writeCharacter called with octetToData=true).
     */
    private static function parseXmlToRole(\DOMElement $el): array
    {
        $result = [];
        foreach ($el->childNodes as $child) {
            if (! ($child instanceof \DOMElement)) continue;

            $tag = $child->tagName;
            $key = preg_match('/^item(\d+)$/', $tag, $m) ? (int) $m[1] : $tag;

            if ($child->hasAttribute('type') && $child->hasAttribute('value')) {
                $result[$key] = [
                    'type'  => $child->getAttribute('type'),
                    'value' => $child->getAttribute('value'),
                ];
            } else {
                $result[$key] = self::parseXmlToRole($child);
            }
        }
        return $result;
    }

    protected function authorize_(string $perm): void
    {
        if (! auth('panel')->user()->can($perm)) {
            abort(403, "Missing permission: {$perm}");
        }
    }
}
