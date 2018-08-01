/**
     * @param {{ $type }} $value
     * @return {{ $model }}
     */
    public function set{{ ucfirst(\Illuminate\Support\Str::camel($name)) }}({{ $type }} $value): {{ $model }}
    {
        $this->setAttribute(self::ATTRIBUTE_{{ strtoupper($name) }}, $value);

        return $this;
    }
