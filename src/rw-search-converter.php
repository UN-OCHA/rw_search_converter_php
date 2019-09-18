<?php

global $apiUrl;
$apiUrl = 'https://api.reliefweb.int/v1/';

global $paths;
$paths = [
  'headlines' => [
    'resource' => 'reports',
    'filter' => [
      'field' => 'headline'
    ]
  ],
  'headlines/thumb' => [
    'resource' => 'reports',
    'filter' => [
      'field' => 'headline.image'
    ]
  ],
  'updates' => [
    'resource' => 'reports'
  ],
  'updates/no-thumb' => [
    'resource' => 'reports',
    'filter' => [
      'field' => 'format.id',
      'value' => [12, 12570, 38974],
      'operator' => 'OR',
      'negate' => true
    ]
  ],
  'maps' => [
    'resource' => 'reports',
    'filter' => [
      'field' => 'format.id',
      'value' => [12, 12570, 38974],
      'operator' => 'OR'
    ]
  ],
  'countries' => [
    'resource' => 'countries'
  ],
  'disasters' => [
    'resource' => 'disasters'
  ],
  'organizations' => [
    'resource' => 'sources'
  ],
  'jobs' => [
    'resource' => 'jobs'
  ],
  'training' => [
    'resource' => 'training'
  ],
  'training/free' => [
    'resource' => 'training',
    'filter' => [
      'field' => 'cost',
      'value' => 'free'
    ]
  ],
  'training/online' => [
    'resource' => 'training',
    'filter' => [
      'field' => 'format.id',
      'value' => 4607
    ]
  ],
  'training/workshop' => [
    'resource' => 'training',
    'filter' => [
      'field' => 'type.id',
      'value' => 4609
    ]
  ],
  'training/academic' => [
    'resource' => 'training',
    'filter' => [
      'field' => 'type.id',
      'value' => 4610
    ]
  ]
];

// API resources.
//
// The format is [shortcut, operator, API field, fixed values (optional)].
global $resources;
$resources = [
 'reports' => [
   'primary_country' => ['PC', 'AND', 'primary_country.id'],
   'country' => ['C', 'AND', 'country.id'],
   'source' => ['S', 'AND', 'source.id'],
   'source_type' => ['ST', 'OR', 'source.type.id'],
   'theme' => ['T', 'AND', 'theme.id'],
   'format' => ['F', 'OR', 'format.id'],
   'disaster' => ['D', 'OR', 'disaster.id'],
   'disaster_type' => ['DT', 'AND', 'disaster_type.id'],
   'vulnerable_groups' => ['VG', 'OR', 'vulnerable_groups.id'],
   'language' => ['L', 'OR', 'language.id'],
   'date' => ['DO', 'AND', 'date.original'],
   'created' => ['DA', 'AND', 'date.created'],
   'feature' => ['FE', 'OR', 'feature.id']
 ],
 'countries' => [
   'status' => ['SS', 'OR', 'status', ['current', 'normal']]
 ],
 'disasters' => [
   'country' => ['C', 'AND', 'country.id'],
   'type' => ['DT', 'OR', 'type.id'],
   'status' => ['SS', 'OR', 'status', ['current', 'past']],
   'date' => ['DA', 'AND', 'date.created']
 ],
 'organizations' => [
   'country' => ['C', 'OR', 'country.id'],
   'source_type' => ['T', 'OR', 'type.id']
 ],
 'jobs' => [
   'type' => ['TY', 'OR', 'type.id'],
   'career_categories' => ['CC', 'OR', 'career_categories.id'],
   'experience' => ['E', 'OR', 'experience.id'],
   'theme' => ['T', 'OR', 'theme.id'],
   'country' => ['C', 'OR', 'country.id'],
   'source' => ['S', 'OR', 'source.id'],
   'source_type' => ['ST', 'OR', 'source.type.id'],
   'closing' => ['DC', 'AND', 'date.closing'],
   'created' => ['DA', 'AND', 'date.created']
 ],
 'training' => [
   'type' => ['TY', 'OR', 'type.id'],
   'career_categories' => ['CC', 'OR', 'career_categories.id'],
   'format' => ['F', 'AND', 'format.id'],
   'cost' => ['CO', 'OR', 'cost', ['fee-based', 'free']],
   'theme' => ['T', 'OR', 'theme.id'],
   'country' => ['C', 'OR', 'country.id'],
   'source' => ['S', 'OR', 'source.id'],
   'training_language' => ['TL', 'OR', 'training_language.id'],
   'created' => ['DA', 'AND', 'date.created'],
   'start' => ['DS', 'AND', 'date.start'],
   'end' => ['DE', 'AND', 'date.end'],
   'registration' => ['DR', 'AND', 'date.registration'],
   'language' => ['L', 'OR', 'language.id'],
   'source_type' => ['ST', 'OR', 'source.type.id']
 ]
];

// Advanced search operator.
global $operators;
$operators = [
 '(' => 'WITH',
 '!(' => 'WITHOUT',
 ')_(' => 'AND WITH',
 ')_!(' => 'AND WITHOUT',
 ').(' => 'OR WITH',
 ').!(' => 'OR WITHOUT',
 '.' => 'OR',
 '_' => 'AND'
];

// Advanced parsing pattern.
global $pattern;
$pattern = '/(!?\(|\)[._]!?\(|[._])([A-Z]+)(\d+-\d*|-?\d+)/';

/**
 * Parse an advanced search date range and return a range array [to, from].
 */
function parseDateRange($value) {
  $values = explode('-', $value, 2);
  $output = new stdClass();
  if (!empty($values[0])) {
    $output->from = substr($values[0], 0, 4) . '-' . substr($values[0], 4, 2) . '-' . substr($values[0], 6, 2);
  }
  if (!empty($values[1])) {
    $output->to = substr($values[1], 0, 4) . '-' . substr($values[1], 4, 2) . '-' . substr($values[1], 6, 2);
  }
  return $output;
}

/**
 * Parse an advanced search query string into API filters.
 */
function parseAdvancedQuery($fields, $query) {
  global $pattern;
  global $operators;
  if (!$query) {
    return null;
  }

  // Map field shortcut to field info.
  $mapping = [];
  foreach ($fields as $key => $value) {
    $mapping[$value[0]] = [
      'field' => $value[2],
      'date' => strpos($value[2], 'date') !== FALSE
    ];
  }

  $root = [
    'conditions' => [],
    'operator' => 'AND'
  ];
  $filter = null;

  $matches = [];
  preg_match_all($pattern, $query, $matches, PREG_PATTERN_ORDER);
  foreach ($matches[0] as $i => $str_match) {

    $operator = $operators[$matches[1][$i]];
    $info = $mapping[$matches[2][$i]];
    $field = $info['field'];
    $value = $info['date'] ? parseDateRange($matches[3][$i]) : intval($matches[3][$i]);

    // Create the API filter.
    if (strpos($operator, 'WITH') !== FALSE) {
      $newFilter = [
        'conditions' => [],
        'operator' => 'AND'
      ];

      if (strpos($operator, 'OUT') !== FALSE) {
        $newFilter['negate'] = true;
      }

      // New nested conditional filter.
      $operator = strpos($operator, 'OR') !== FALSE ? 'OR' : 'AND';
      if ($operator !== $root['operator']) {
        $root = [
          'conditions' => [$root],
          'operator' => $operator
        ];
      }
      $root['conditions'][] = $newFilter;
      $length = count($root['conditions']);
      $filter = &$root['conditions'][$length - 1];
    }

    // Add value.
    if ($filter) {
      $filter['operator'] = $operator;
      $filter['conditions'][] = [
        'field' => $field,
        'value' => $value
      ];
    }
  }
  $length = count($root['conditions']);
  return $length > 0 ? $root : null;
}

/**
 * Reduce nested filters.
 */
function optimizeFilter($filter) {
  if ($filter && isset($filter['conditions'])) {
    $conditions = [];
    $length = count($filter['conditions']);
    for ($i = 0, $l = $length; $i < $l; $i++) {
      $condition = optimizeFilter($filter['conditions'][$i]);
      if ($condition) {
        $conditions[] = $condition;
      }
    }

    if (count($conditions)) {
      $conditions = combineConditions($filter['operator'], $conditions);

      if (count($conditions) == 1) {
        $condition = $conditions[0];
        if (isset($filter['negate']) && $filter['negate'] == true) {
          $condition['negate'] = true;
        }
        $filter = $condition;
      }
      else {
        $filter['conditions'] = $conditions;
      }
    }
    else {
      $filter = null;
    }
  }
  return $filter;
}

/**
 * Combine simple filter conditions to shorten the filters.
 */
function combineConditions($operator = 'AND', $conditions) {
  $filters = [];
  $result = [];

  $length = count($conditions);
  for ($i = 0, $l = $length; $i < $l; $i++) {
    $condition = $conditions[$i];
    $field = isset($condition['field']) ? $condition['field'] : '';
    $value = isset($condition['value']) ? $condition['value'] : '';

    // Nested conditions - flatten the condition's conditions.
    if (isset($condition['conditions'])) {
      $condition['conditions'] = combineConditions($condition['operator'], $condition['conditions']);
      $result[] = $condition;
    }
    // Existence filter - keep as is.
    elseif (!$value) {
      $result[] = $condition;
    }
    // Range filter - keep as is.
    elseif (!is_array($value)) {
       $result[] = $condition;
    }
    // Different operator or negated condition -  keep as is.
    elseif ($condition['operator'] !== $operator || isset($condition['negate'])) {
      $result[] = $condition;
    }
    else {
      // Store the values for the field to combine later.
      if (!isset($filters[$field])) {
        $filters[$field] = array();
      }
      $filters[$field] = array_merge($filters[$field], $value);
    }
  }

  foreach ($filters as $field => $value) {
    $filter = [
      'field' => $field
    ];
    // Ensure there are only unique values
    /*if (Array.isArray(value)) {
      value = [... new Set(value)];
    }*/
    if (count($value) == 1) {
      $filter['value'] = $value[0];
    }
    else {
      $filter['value'] = $value;
      $filter['operator'] = $operator;
    }
    $result[] = $filter;
  }
  return $result;
}


/**
 * Return the query string representation of an API filter.
 */
function stringifyFilter($filter) {
  if (!$filter) {
    return '';
  }

  $result = '';
  $operator = isset($filter['operator']) ? $filter['operator'] : '';
  $operator = ' ' . $operator . ' ';

  if (isset($filter['conditions'])) {
    $group = [];
    for ($i = 0, $l = count($filter['conditions']); $i < $l; $i++) {
      $group[] = stringifyFilter($filter['conditions'][$i]);
    }
    $result = '(' . implode($operator, $group) . ')';
  }
  else if (!isset($filter['value'])) {
    $result = '_exists_:' . isset($filter['field']) ? $filter['field'] : '';
  }
  else {
    $value = $filter['value'];
    if (is_array($value)) {
      $value = '(' . implode($operator, $value) . ')';
    }
    // Date.
    elseif (is_object($value)) {
      $from = isset($value->from) ? substr($value->from, 0, 10) : '';
      $to = isset($value->to) ? substr($value->to, 0, 10) : '';
      if (!$from) {
        $value = '<' . $to;
      }
      elseif (!$to) {
        $value = '>=' . $from;
      }
      else {
        $value = '[' . $from . ' TO ' . $to . '}';
      }
    }

    $result = $filter['field'] . ':' . $value;
  }
  return (isset($filter['negate']) ? 'NOT ' : '') . $result;
}

/**
 * Convert facets parameters to an API filter.
 */
function convertFacets($fields, $params) {
  $conditions = [];

  foreach ($params as $key => $value) {
    if (isset($fields[$key])) {

      $shortcut = $fields[$key][0];
      $operator = $fields[$key][1];
      $field = $fields[$key][2];
      $values = isset($fields[$key][3]) ? $fields[$key][3] : array();
      $pvalue = NULL;

      // Date field - parse range format.
      if (strpos($field, 'date') !== FALSE) {
        $pvalue = parseDateRange($value);
      }
      // Term reference fields - ensure the term id is an integer.
      else if (substr($field, -3) == '.id') {
        $pvalue = array();
        $vals = explode('.', $value);
        foreach ($vals as $val) {
          if (intval($val) !== 0) {
            $pvalue[] = intval($val);
          }
        }
      }
      // Fixed values fields - ensure the value(s) are in the list.
      else if (!empty($values)) {
        $pvalue = array();
        $vals = explode('.', $value);
        foreach ($vals as $val) {
          if (in_array($val, $values)) {
            $pvalue[] = $val;
          }
        }
      }
      // Skip unrecognized fields.
      else {
        continue;
      }

      if ($pvalue && !empty($pvalue)) {
        $conditions[] = [
          'field' => $field,
          'operator' => $operator,
          'value' => $pvalue
        ];
      }
    }
  }
  return count($conditions) ? ['conditions' => $conditions, 'operator' => 'AND'] : null;
}



/**
 * Remove excessive outer parentheses.
 */
function trimFilter($filter) {
  return strpos($filter, '(') === 0 ? substr($filter, 1, strlen($filter) - 2) : $filter;
}

/**
 * Get the API query string from the converted search and filter.
 */
function getQueryString($search, $filter) {
  $filters = [];
  if (!empty($search)) {
    $filters[] = '(' . $search . ')';
  }
  $stringifiedFilter = stringifyFilter($filter);
  if (!empty($stringifiedFilter)) {
    $filters[] = $stringifiedFilter;
  }

  $query = '';
  if (count($filters) > 1) {
    $query = implode(' AND ', $filters);
  }
  else if (count($filters) === 1) {
    $query = trimFilter($filters[0]);
  }
  return $query;
}



/**
 * Get the API url from the generated query string.
 */
function getApiUrl($resource, $query, $appname) {
  global $apiUrl;
  $params = [
    'appname=' . $appname,
    'profile=list',
    'preset=latest',
    'slim=1'
  ];
  if ($query) {
    $params[] = 'query[value]=' . urlencode($query);
    $params[] = 'query[operator]=AND';
  }

  return $apiUrl . $resource . '?' . implode('&', $params);
}


/**
 * Convert a search query to an API query.
 */
function convertToAPI($url, $appname) {
  global $paths;
  global $resources;
  if (!$url) {
    return;
  }

  $url = parse_url($url);
  $params = null;
  parse_str($url['query'], $params);
  $path = preg_replace("#^[/]+|[/]+$#", '', $url['path']);

  // Skip if the resource couldn't be determined.
  if (!$paths[$path]) {
    return;
  }

  $path = $paths[$path];
  $resource = $path['resource'];
  $fields = $resources[$resource];

  // Search query
  $search = '';
  if (isset($params['search'])) {
    $search = trim($params['search']);
  }

  // Advanced search, facets and resource pre-filter combined filter.
  $conditions = [];
  if (isset($params['advanced-search']) && !empty($params['advanced-search'])) {
    $conditions[] = parseAdvancedQuery($fields, $params['advanced-search']);
  }
  $condition2 = convertFacets($fields, $params);
  if (!empty($condition2)) {
    $conditions[] = $condition2;
  }
  if (isset($path['filter'])) {
    $conditions[] = $path['filter'];
  }
  $filter = optimizeFilter([
    'operator' => 'AND',
    'conditions' => $conditions
  ]);

  // Query string.
  $query = getQueryString($search, $filter);

  // API url.
  $ourl = getApiUrl($resource, $query, $appname);

  return $ourl;
}
