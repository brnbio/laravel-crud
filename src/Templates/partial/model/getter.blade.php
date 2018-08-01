/**
     * @return {{ $type }}
     */
    public function get{{ ucfirst(\Illuminate\Support\Str::camel($name)) }}(): {{ $type }}
    {
        return $this->getAttribute(self::ATTRIBUTE_{{ strtoupper($name) }});
    }
