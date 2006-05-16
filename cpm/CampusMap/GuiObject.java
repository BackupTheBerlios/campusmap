package CampusMap;

import java.awt.event.MouseEvent;
import java.lang.reflect.Method;
import java.util.Vector;
import processing.core.*;

//class to manage the guiObjects
public class GuiObject {
	final int TOLERANCE = 4;

	static boolean		isInUse;	//shows if one of the GuiObjects is in use
	protected PApplet		applet;		//the reference to the applet
	protected int id;
	protected int x,y,w,h;				//hor./vert position, width and height
	protected int minVal,maxVal,val;		//minumum/maximum/current absolute value (on the screen)
	protected boolean grabbed;			//if this is grabbed atm
	protected boolean vertical;			//if this sliders is shown vertical or horizontal
	protected Color activeCol,inactiveCol;//the colors of this slider
	protected boolean active;				//if this slider is used (drawn and controlable)
	protected boolean visible;
	protected String helpText;
	protected Object executeObject;
	protected Method executeMethod;

	public GuiObject(int p_x, int p_y, int p_w, int p_h, String hlp_p) {
		x		=	p_x;
		y		= 	p_y;
		w		= 	p_w;
		h		= 	p_h;
		//applet.env.objectInitDisplay.setText("GuiObjects");
		setColor(200,0,0, 200,200,200);
		helpText = hlp_p;
		active 	= true;
		visible = true;
		isInUse	= false;
	}
	
	/**
	 * @return Returns the visible.
	 */
	public boolean isVisible() {
		return visible;
	}

	/**
	 * @param visible The visible to set.
	 */
	public void setVisible(boolean visible) {
		this.visible = visible;
		System.out.println("Button sichtbar."+visible);
	}



	public void setId(int p_id){
		id		=	p_id;
	}
	public int getId() {
		return id;
	}
	
	public boolean check(int mouseX, int mouseY, boolean mouseJustReleased, boolean mouseJustPressed){
		boolean back=false;
		if( mouseX>x && mouseX<x+w && mouseY>y 	&& mouseY<y+h)back=true;
		return back;
	}

	
	public void mouseEvent(MouseEvent e) {
		boolean mouseJustPressed	= false;
		boolean mouseJustReleased	= false;
		
		switch (e.getID()) {
	    case MouseEvent.MOUSE_PRESSED:
	    	mouseJustPressed = true;
	    	break;
	    case MouseEvent.MOUSE_RELEASED:
	    	mouseJustReleased = true;
	    	break;
		}
		check(e.getX(), e.getY(), mouseJustReleased, mouseJustPressed);
	}
	

	public boolean registerExecution(String methodName, Object objectReference, CampusMap applet) {
		executeObject = objectReference;
		Float floatValue = new Float(0);
		Boolean boolValue = new Boolean(true);
		try {
			executeMethod = executeObject.getClass().getMethod(methodName, new Class[] {floatValue.getClass(), boolValue.getClass()});
		} catch (Exception e) {
			return false;
	    }
		return true;
	}
	
	public void execute(boolean mouseJustPressed) {
		if (executeMethod != null && active) {
		    try {
		    	executeMethod.invoke(executeObject, new Object[]{} );
		    }	catch (Exception e) {
			      System.err.println("Disabling execute GuiObject event because of an error.");
			      e.printStackTrace();
			      executeMethod = null;
		    }
		}
	}
	
	public void setColor(int r1,int g1,int b1, int r2,int g2,int b2) {
		activeCol	= new Color(r1,g1,b1,255);
		inactiveCol	= new Color(r2,g2,b2,255);
	}

	public void setActive(boolean _active) {
		active = _active;
	}

	public boolean getActive() {
		return active;
	}
	
	static public boolean getIsInUse() {
		return isInUse;
	}
	
	static public void setIsInUse(boolean state) {
		isInUse = state;
	}
	/**
	 * To be overwritten
	 *
	 */
	public void draw(PApplet applet){
		/**/
	}
	
	

} //end of class GuiObjects
