<!--
 Pharinix Copyright (C) 2016 Pedro Pelaez <aaaaa976@gmail.com>
Sources https://github.com/PSF1/pharinix

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
-->
<div class="content">
    <h1><?php __e('Pharinix installer'); ?></h1>
    <form action='<?php echo CMS_DEFAULT_URL_BASE; ?>' method="post">
        <input type="hidden" name="command" value="install">
        <input type="hidden" name="interface" value="echoJson">
        
        <legend><?php __e('Root user configuration'); ?></legend>
        <div class="form-group">
            <label for="root"><?php __e('Login email'); ?></label>
            <input type="email" class="form-control" name="root" placeholder="<?php __e('Email'); ?>">
        </div>
        <div class="form-group">
            <label for="rootpass"><?php __e('Password'); ?></label>
            <input type="password" class="form-control" name="rootpass" placeholder="<?php __e('Password'); ?>">
        </div>
        
        <legend><?php __e('Database configuration'); ?></legend>
        <div class="form-group">
            <label for="dbhost"><?php __e('Host'); ?></label>
            <input type="text" class="form-control" name="dbhost" placeholder="127.0.0.1">
        </div>
        <div class="form-group">
            <label for="dbschema"><?php __e('Database name'); ?></label>
            <input type="text" class="form-control" name="dbschema" placeholder="<?php __e('Database name'); ?>">
        </div>
        <div class="form-group">
            <label for="dbuser"><?php __e('User'); ?></label>
            <input type="text" class="form-control" name="dbuser" placeholder="<?php __e('User'); ?>">
        </div>
        <div class="form-group">
            <label for="dbpass"><?php __e('Password'); ?></label>
            <input type="password" class="form-control" name="dbpass" placeholder="<?php __e('Password'); ?>">
        </div>
        
        <button type="submit" class="btn btn-primary"><?php __e('Install'); ?></button>
    </form>
</div>