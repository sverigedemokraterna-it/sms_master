<?php

/**
	@brief		XHTML table class.
	
	Allows tables to be made created, modified and displayed efficiently.
	
	@par		Example 1
	
	`$table = new sd_table();
	$table->caption()->text( 'This is a caption' );
	$tr = $table->head()->row();
	$tr->th()->text( 'Name' );
	$tr->th()->text( 'Surname' );
	foreach( $names as $name )
	{
		$tr = $table->body()->row();
		$tr->td()->text( $name->first );
		$tr->td()->text( $name->last );
		
		// Or...
		$table->body()->row()				// Create a new row.
			->td()->text( $name->first )
			->row()							// Still the same row, used for chaining.
			->td()->text( $name->last )
	}
	`
	
	@par		Example 2 - How about some styling?
	
	`$tr->td()->text( $name->first )->css_style( 'font-weight: bold' );`
	
	@par		Example 3 - How about some CSS classing??
	
	`$tr->td()->text( $name->first )->css_class( 'align_center' )->css_style( 'font-size: 200%;' );`
		
	@author		Edward Plainview <edward.plainview@sverigedemokraterna.se>
	@license	GPL v3
	
	@par		Changelog
	
	- @b 2013-04-08		First release.
**/

class sd_table
{
	use sd_table_object;
	
	/**
		@brief		The sd_table_section_body object.
		@var		$body
	**/
	protected $body;
	
	/**
		@brief		The sd_table_section_foot object.
		@var		$foot
	**/
	protected $foot;
	
	/**
		@brief		The sd_table_section_head object.
		@var		$head
	**/
	protected $head;
	
	/**
		@brief		How many tabs to apply before each output line.
		@var		$tabs
	**/
	public $tabs = 0;
	
	/**
		@brief		Object / element HTML tag.
		@var		$tag
	**/
	protected $tag = 'table';
	
	public function __construct()
	{
		$this->caption = new sd_table_caption( $this );
		$this->body = new sd_table_section_body( $this );
		$this->foot = new sd_table_section_foot( $this );
		$this->head = new sd_table_section_head( $this );
	}
	
	public function __toString()
	{
		$rv = '';
		$rv .= $this->open_tag();
		$rv .= $this->caption . $this->head . $this->foot . $this->body;
		$rv .= $this->close_tag();
		return $rv;
	}
	
	/**
		@brief		Return the body section.
		@return		sd_table_section_body		The table section of the table.
	**/
	public function body()
	{
		return $this->body;
	}
	
	/**
		@brief		Return the caption object of the table.
		@return		sd_table_caption		The table's caption.
	**/
	public function caption()
	{
		return $this->caption;
	}
	
	/**
		@brief		Return the foot section.
		@return		sd_table_section_foot		The table section of the table.
	**/
	public function foot()
	{
		return $this->foot;
	}
	
	/**
		@brief		Return the head section.
		@return		sd_table_section_head		The head section of the table.
	**/
	public function head()
	{
		return $this->head;
	}
}

/**
	@brief		Table caption.
**/
class sd_table_caption
{
	use sd_table_object;
	
	/**
		@brief		Object / element HTML tag.
		@var		$tag
	**/
	protected $tag = 'caption';
	
	public function __construct( $table )
	{
		$this->table = $table;
		$this->tabs = $this->table->tabs + 1;
	}
	
	public function __toString()
	{
		if ( $this->text == '' )
			return '';
		return $this->open_tag() . $this->tabs( $this->tabs + 1 ) . $this->text . "\n" . $this->close_tag();
	}
}

/**
	@brief		A table cell.
	
	This is a superclass for the th and td subclasses.
**/
class sd_table_cell
{
	use sd_table_object;
	
	/**
		@brief		Unique ID of this cell.
		@var		$id
	**/
	public $id;
	
	protected $tag = 'cell';
	
	public $row;
	
	public function __construct( $sd_table_row, $id = null )
	{
		if ( $id === null )
			$id = 'c' . $this->random_id();
		$this->id = $id;
		$this->row = $sd_table_row;
		$this->tabs = $this->row->tabs + 1;
	}
	
	public function __tostring()
	{
		$this->set_attribute( 'id', $this->id );
		return $this->open_tag() . $this->tabs( $this->tabs + 1 ) . $this->text . "\n" . $this->close_tag();
	}
	
	/**
		@brief		Return the sd_table_row of this cell.
		
		Is used to continue the ->td()->row()->td() chain.
		
		@return		sd_table_row		The table row this cell was created in.
	**/
	public function row()
	{
		return $this->row;
	}
	
}	

/**
	@brief		Cell of type TD.
**/
class sd_table_cell_td
	extends sd_table_cell
{
	protected $tag = 'td';
}

/**
	@brief		Cell of type TH.
**/
class sd_table_cell_th
	extends sd_table_cell
{
	protected $tag = 'th';
}

class sd_table_row
{
	use sd_table_object;
	
	/**
		@brief		Array of cells.
		@var		$cells
	**/
	public $cells;
	
	/**
		@brief		Unique ID of this row.
		@var		$id
	**/
	public $id;
	
	/**
		@brief		Parent section.
		@var		$section
	**/
	public $section;
	
	/**
		@brief		Object / element tag.
		@var		$tag
	**/
	protected $tag = 'tr';

	public function __construct( $sd_table_section, $id = null )
	{
		if ( $id === null )
			$id = 'r' . $this->random_id();
		$this->id = $id;
		$this->cells = array();
		$this->section = $sd_table_section;
		$this->tabs = $this->section->tabs + 1;
	}
	
	public function __tostring()
	{
		if ( count( $this->cells ) < 1 )
			return '';
		
		$rv = '';
		$this->set_attribute( 'id', $this->id );
		$rv .= $this->open_tag();
		foreach( $this->cells as $cell )
			$rv .= $cell;
		$rv .= $this->close_tag();
		
		return $rv;
	}
	
	/**
		@brief		Add a cell to the cell array.
		@param		sd_table_cell		The table cell to add.
		@return		sd_table_cell		The table cell just added.
	**/
	public function cell( $sd_table_cell )
	{
		$this->cells[ $sd_table_cell->id ] = $sd_table_cell;
		return $sd_table_cell;
	}
	
	
	/**
		@brief		Create a new td cell, with an optional id.
		@param		string		$id			The HTML ID of the td.
		@return		sd_table_cell_td		The newly created td.
	**/
	public function td( $id = null )
	{
		$td = new sd_table_cell_td( $this, $id );
		return $this->cell( $td );
	}
	
	/**
		@brief		Create a new th cell, with an optional id.
		@param		string		$id			The HTML ID of the th.
		@return		sd_table_cell_th		The newly created th.
	**/
	public function th( $id = null )
	{
		$th = new sd_table_cell_th( $this, $id );
		return $this->cell( $th );
	}
}

/**
	@brief		A table section: the thead or tbody.
**/
class sd_table_section
{
	use sd_table_object;

	/**
		@brief		Array of sd_table_rows.
		@var		$rows
	**/
	public $rows;
	
	/**
		@brief		Parent sd_table.
		@var		$table
	**/
	public $table;
	
	/**
		@brief		Object / element HTML tag.
		@var		$tag
	**/
	protected $tag = '';
	
	public function __construct( $table )
	{
		$this->table = $table;
		$this->rows = array();
		$this->tabs = $this->table->tabs + 1;
	}
		
	public function __tostring()
	{
		if ( count( $this->rows ) < 1 )
			return '';
			
		$rv = '';
		$rv .= $this->open_tag();
		foreach( $this->rows as $row )
			$rv .= $row;
		$rv .= $this->close_tag();
		return $rv;
	}
	
	/**
		@brief		Create a new row, with an optional id.
		@param		string		$id		The HTML ID of this row.
		@return		sd_table_row		The newly created row.
	**/
	public function row( $id = null )
	{
		$row = new sd_table_row( $this, $id );
		$this->rows[ $row->id ] = $row;
		return $row;
	}
}

class sd_table_section_body
	extends sd_table_section
{
	protected $tag = 'tbody';
}

class sd_table_section_foot
	extends sd_table_section
{
	protected $tag = 'tfoot';
}

class sd_table_section_head
	extends sd_table_section
{
	protected $tag = 'thead';
}

/**
	@brief		Used for setting attributes and handling open / close tags.
**/
trait sd_table_object
{
	/**
		@brief		Parent section.
		@var		$section
	**/
	protected $attributes = array();
	/**
		@brief		Parent section.
		@var		$section
	**/
	protected $css_class = array();
	
	/**
		@brief		Text / contents of this object.
		@var		$text
	**/
	protected $text = '';
	
	/**
		@brief		Append a text to an attribute.
		
		Text is appended with a space between.
		
		@param		string		$type		Type of attribute.
		@param		string		$text		Attribute text to append.
		@return		$this
	**/
	public function append_attribute( $type, $text )
	{
		$text = $this->get_attribute( $type ) . ' ' . $text;
		$text = trim( $text );
		return $this->set_attribute( $type, $text );
	}
	
	/**
		@brief		Convenience function to append an attribute.
		@param		string		$type		Type of attribute.
		@param		string		$text		Attribute text.
		@return		$this
		@see		append_attribute
	**/
	public function attribute( $type, $text )
	{
		return $this->append_attribute( $type, $text );
	}
	
	/**
		@brief		Output a string that closes the tag of this object.
		@return		string		The closed tag.
	**/
	public function close_tag()
	{
		return sprintf( '%s</%s>%s', $this->tabs(), $this->tag, "\n" );
	}
	
	/**
		@brief		Convenience function to set colspan property.
		
		Should only be used on cells.
		
		@param		string		$colspan		How much the object should colspan.
		@return		$this
	**/
	public function colspan( $colspan )
	{
		return $this->set_attribute( 'colspan', $colspan );
	}
	
	/**
		@brief		Convenience function to add another CSS class to this object.
		@param		string		$css_class		A CSS class or classes to append to the object.
		@return		$this
	**/
	public function css_class( $css_class )
	{
		return $this->append_attribute( 'class', $css_class );
	}
	
	/**
		@brief		Convenience function to add another CSS style to this object.
		@param		string		$css_style		A CSS style string to append to this object.
		@return		$this
	**/
	public function css_style( $css_style )
	{
		$style = $this->get_attribute( 'style' );
		$style .= '; ' . $css_style;
		$style = trim( $style, '; ' );
		$style = preg_replace( '/;;/m', ';', $style );
		return $this->append_attribute( 'style', $style );
	}
	
	/**
		@brief		string		$type		Type of attribute (key).
		@return		false|string			False if there is no attribute of that type set, or whatever is set.
	**/
	public function get_attribute( $type )
	{
		if ( ! isset( $this->attributes[ $type ] ) )
			return false;
		else
			return $this->attributes[ $type ];
	}
	
	/**
		@brief		Convenience function to set header property.
		
		The header property of a td cell is an accessability feature that tells screen readers which th headers this cell is associated with.
		
		@param		string		$header		The ID or IDs (spaced) with which this cell is associated.
		@return		$this
	**/
	public function header( $header )
	{
		return $this->set_attribute( 'header', $header );
	}
	
	/**
		@brief		Opens the tag of this object.
		
		Will take care to include any attributes that have been set.
	**/
	public function open_tag()
	{
		$attributes = array();
		
		foreach( $this->attributes as $key => $value )
			if ( $value != '' )
				$attributes[] = sprintf( '%s="%s"', $key, trim( $value ) );
			
		if ( count( $attributes ) > 0 )
			$attributes = ' ' . implode( ' ', $attributes );
		else
			$attributes = '';
		
		return sprintf( '%s<%s%s>%s', $this->tabs(), $this->tag, $attributes, "\n" );
	}
	
	public function random_id()
	{
		$id = md5( microtime() . rand( 1000, 9999 ) );
		$id = substr( $id, 0, 8 );
		return $id;
	}
	
	/**
		@brief		Clears and resets an attribute with new text.
		
		@param		string		$type		Type of attribute.
		@param		string		$text		Attribute text to set.
		@return		$this
	**/
	public function set_attribute( $type, $text )
	{
		$this->attributes[ $type ] = $text;
		return $this;
	}
	
	/**
		@brief		Returns a string of tabs.
		
		@param		int		$tabs		How many tabs to return. If null, uses the $tabs property of the object.
	**/
	public function tabs( $tabs = null )
	{
		if ( $tabs === null )
			$tabs = $this->tabs;
		return str_pad( '', $tabs, "\t" );
	}
	
	/**
		@brief		Sets the text of this object.
		
		The text is the contents of this object, most often an HTML string.
		
		@param		string		$text		Text to set.
		@return		$this
	**/
	public function text( $text )
	{
		$this->text = $text;
		return $this;
	}
	
	/**
		@brief		Sets the text of this object using sprintf.
		
		The $text and all extra parameters is run through sprintf as convenience.
		
		@param		string		$text		Text to set via sprintf.
		@return		$this
		@see		text()
	**/
	public function textf( $text )
	{
		return $this->text( call_user_func_array( 'sprintf', func_get_args() ) );
	}
	
	/**
		@brief		Convenience function to set the hoverover title property.
		@param		string		$title		Title to set.
		@return		$this
	**/
	public function title( $title )
	{
		return $this->attribute( 'title', $title );
	}
	
}

