<?php

return ['
    /**
     * @return BelongsTo
     */
    public function ${FUNCTION}(): BelongsTo
    {
        return $this->belongsTo(${MODEL}::class); 
    }'];