<?php

namespace Mbsoft\OpenAlex;

class OpenAlex {
    public function works(): Builder
    {
        return new Builder('works');
    }

    public function authors(): Builder
    {
        return new Builder('authors');
    }

    public function sources(): Builder
    {
        return new Builder('sources');
    }

    public function institutions(): Builder
    {
        return new Builder('institutions');
    }

    public function topics(): Builder
    {
        return new Builder('topics');
    }
}
