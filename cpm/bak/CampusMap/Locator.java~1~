package CampusMap;
/*
 * Created on 31.05.2005
 *
 * TODO To change the template for this generated file go to
 * Window - Preferences - Java - Code Style - Code Templates
 */

/**
 * @author David
 *
 * TODO To change the template for this generated type comment go to
 * Window - Preferences - Java - Code Style - Code Templates
 */
//class to find a position with the mouse in two steps:  a) x/y  b) z

class Locator {
	
	private final static int timeToWaitAfterClick = 5000;

	private CampusMap applet;
	
	private FVector position;
	private FVector lastStoredPosition;

	private boolean	active;
	private boolean x_and_y_set;
	private boolean z_set;
	
	private int	mouse_y_stored;
	private int timeClicked;


	public Locator(CampusMap p_applet) {
		applet = p_applet;
		applet.env.objectInitDisplay.setText("Locator");
		position = new FVector(3);
		lastStoredPosition = new FVector(3);
		active = false;
		x_and_y_set = false;
		z_set = false;
		mouse_y_stored = 0;
		timeClicked = 0;
	}

	public void draw() {
		if (active) {
			if (!x_and_y_set) {
				position = applet.theCamera.getMouseGroundPlaneIntersection(false);
				if (position.getX() != 0.0f || position.getY() != 0.0f) {
					drawGroundPos(position);
					if (applet.controls.mouseJustPressed) {
						x_and_y_set = true;
						mouse_y_stored = applet.mouseY;
						applet.theCamera.birdView(position.add(new FVector(0,0,30)), 20.0f, 1200, 40.0f);
						Object[] actionObjects = {new Boolean(true)};
						applet.theCamera.queueAction("setInstantCircleView", 1200, actionObjects);
					}
				}
			} else if (!z_set) {
				position.setZ((mouse_y_stored - applet.mouseY) /2 );
				if (position.getZ() < 0.1f)
				{
					position.setZ(0.1f);
					mouse_y_stored = applet.mouseY;
				}
				drawGroundPos(position);
				drawHeight(position);
				if (applet.controls.mouseJustPressed) {
					z_set = true;
					lastStoredPosition = new FVector(position);
					System.out.println("Locator position stored at: " + lastStoredPosition.toString());
					timeClicked = applet.millis();
				}
			} else {
				drawGroundPos(position);
				drawHeight(position);
				if ( (timeClicked + timeToWaitAfterClick) > applet.millis() ) {
					applet.theCamera.setInstantCircleView(new Boolean(false));
					active = false;
					x_and_y_set = false;
					z_set = false;
				}
			}
		}
	}
	
	private void drawGroundPos(FVector pos) {
		applet.pushMatrix();
		applet.translate(0.0f, 0.0f, 0.1f);
		applet.stroke(255,0,0);
		applet.fill(255);
		applet.ellipse(pos.e[0], pos.e[1],25.0f,25.0f);
		applet.popMatrix();
	}
	
	private void drawHeight(FVector pos) {
		applet.pushMatrix();
		applet.fill(255, 255, 0, 255);
		applet.noStroke();
		applet.translate(pos.e[0], pos.e[1], 0.0f);
		applet.box(4, 4, 1000);
		applet.popMatrix();

		applet.pushMatrix();
		applet.fill(255);
		applet.stroke(0,255,0,255);
		applet.translate(0.0f, 0.0f, pos.e[2]);
		applet.ellipse(pos.e[0], pos.e[1],25.0f,25.0f);
		applet.popMatrix();
	}

	public boolean activate() {
		if (active)	return false;
		active = true;
		return true;
	}
	
	public FVector getLastPosition() {
		return lastStoredPosition;
	}
	
} //end of class Locator
