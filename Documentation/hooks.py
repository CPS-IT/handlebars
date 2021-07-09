"""
This file is part of the TYPO3 CMS extension "handlebars".

Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
"""

import datetime

def apply_copyright_year(config: object):
    if 'copyright' in config:
        config['copyright'] = config['copyright'].replace('%current_year%', str(datetime.datetime.now().year))
    return config
