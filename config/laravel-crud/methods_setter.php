<?php

return ['
    /**
     * @param ${TYPE} 
     * @return ${MODEL}
     */
    public function ${FUNCTION}(${TYPE} $value): ${MODEL}
    {
        $this->setAttribute(${ATTRIBUTE}, $value);
        
        return $this;
    }'];