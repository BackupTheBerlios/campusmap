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
//class to draw the boxes for the campusmap logo

class Boxes {

	private final static int boxWidth = 12;
	private final static int gapWidth = 6;
	private final static int moveX = 180;
	private final static int moveY = 768;
	private final static float startHeight = 1.0f;
	private final static float endHeight = 100.f;
	
	private float currentHeight = startHeight;
	
	private int positions[][] = {
			{0,1,1,1,1,0,0,0,1,1,1,1,0,0,1,1,0,0,0,1,1,0,1,1,1,1,1,0,0,1,1,0,0,1,1,0,0,1,1,1,1,0,0,1,1,0,0,0,1,1,0,0,1,1,1,1,0,0,1,1,1,1,1,0},
			{1,1,0,0,1,1,0,1,1,0,0,1,1,0,1,1,1,0,1,1,1,0,1,1,0,0,1,1,0,1,1,0,0,1,1,0,1,1,0,0,0,0,0,1,1,1,0,1,1,1,0,1,1,0,0,1,1,0,1,1,0,0,1,1},
			{1,1,0,0,0,0,0,1,1,0,0,1,1,0,1,1,1,1,1,1,1,0,1,1,0,0,1,1,0,1,1,0,0,1,1,0,0,1,1,1,1,0,0,1,1,1,1,1,1,1,0,1,1,0,0,1,1,0,1,1,0,0,1,1},
			{1,1,0,0,1,1,0,1,1,1,1,1,1,0,1,1,0,1,0,1,1,0,1,1,1,1,1,0,0,1,1,0,0,1,1,0,0,0,0,0,1,1,0,1,1,0,1,0,1,1,0,1,1,1,1,1,1,0,1,1,1,1,1,0},
			{0,1,1,1,1,0,0,1,1,0,0,1,1,0,1,1,0,0,0,1,1,0,1,1,0,0,0,0,0,0,1,1,1,1,0,0,1,1,1,1,1,0,0,1,1,0,0,0,1,1,0,1,1,0,0,1,1,0,1,1,0,0,0,0}
	};

	private CampusMap applet;

	public Boxes(CampusMap p_applet) {
		applet = p_applet;
	}

	public void draw() {
		applet.fill(10, 10, 10, 255);
		applet.noStroke();
		//stroke(200,200,200,150);
		//applet.directionalLight(240, 240, 240, applet.theCamera.getLook().getX(), applet.theCamera.getLook().getY(), applet.theCamera.getLook().getZ());

		for (int y = 0; y < positions.length; y++) {
			for (int x = 0; x < positions[0].length; x++) {
				if (positions[y][x] == 1) {
					
					applet.pushMatrix();
					applet.translate(x * (boxWidth + gapWidth) + moveX, y * (boxWidth + gapWidth) + moveY, currentHeight/2);
					applet.box(boxWidth, boxWidth, 2);
					applet.popMatrix();
				}
			}
		}
	}

} //end of class boxes
