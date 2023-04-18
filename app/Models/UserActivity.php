<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use UAParser\Exception\FileNotFoundException;
use UAParser\Parser;

class UserActivity extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'location' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['ip', 'location', 'agent'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'user_id', 'created_at', 'updated_at'];

    /**
     * Pretty print user agent
     *
     * @return string
     *
     * @throws FileNotFoundException
     */
    protected function getParsedAgentAttribute(): string
    {
        $parser = Parser::create();
        $result = $parser->parse($this->agent);

        return $result->toString();
    }
}
