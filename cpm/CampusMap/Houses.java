package CampusMap;

import processing.core.PApplet;
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
//class to manage the buttons

class Houses {

	private final static int boxWidth = 80;
	private final static int gapWidth = 30;
	
	private int	numHouses;
	
	private FVector[] myPos;
	private float[] myRot;
	private FVector[] myScale;
	
	private int[]	style;

	private boolean[]	active;
	
	private CampusMap applet;

	
	public Houses(CampusMap p_applet, int p_numHouses) {
		
		applet = p_applet;
		applet.env.objectInitDisplay.setText("Explications");

		numHouses = p_numHouses;
	
		myPos	= new FVector[numHouses];
		myRot	= new float[numHouses];
		myScale	= new FVector[numHouses];
		style	= new int[numHouses];
	
		active		= new boolean[numHouses];
		
		FVector[] posVectors = applet.groundPlane.getRandomPositionsOnGroundPlane(numHouses, false, true, 60.0f, 20.0f, true);
	
		for (int i = 0; i < numHouses; i++) {
			myPos[i] = posVectors[i];
			myScale[i]	= new FVector(applet.random(1, 1.4f), applet.random(1, 1.4f), applet.random(1, 1.4f));
			myRot[i]	= applet.random(0, PApplet.PI);
	    	style[i]		= (int)applet.random(0, 2.5f);
	
	    	active[i] = true;
	    }
	}
	
	public void draw() {
		applet.noStroke();

		for (int i = 0; i < numHouses; i++) {
			if (active[i]) {
				switch (style[i]) {
				case 0:
					applet.pushMatrix();
					applet.translate(myPos[i].e[0], myPos[i].e[1], 0);
					applet.scale(myScale[i].e[0], myScale[i].e[1], myScale[i].e[2]);
					applet.rotateZ(myRot[i]);
					applet.translate(0, 0, 12.5f);
					applet.fill(170, 180, 190, 255);
					applet.box(25, 45, 25);
					applet.fill(120, 130, 150, 255);
					applet.box(15, 53, 5);
					applet.translate(0, 0, 12.5f);
					applet.rotateY(PApplet.PI/4);
					applet.fill(150, 100, 100, 255);
					applet.box(17.625f, 44.25f, 17.625f);
					applet.popMatrix();
					break;
				case 1:
					applet.pushMatrix();
					applet.translate(myPos[i].e[0], myPos[i].e[1], 0);
					applet.scale(myScale[i].e[0], myScale[i].e[1], myScale[i].e[2]);
					applet.rotateZ(myRot[i]);
					applet.translate(0, 0, 7.5f);
					applet.fill(130, 140, 160, 255);
					applet.box(15, 15, 15);
					applet.translate(15, 0, 0);
					applet.fill(120, 130, 150, 255);
					applet.box(15, 15, 15);
					applet.translate(0, 0, 7.5f);
					applet.rotateY(PApplet.PI/4);
					applet.fill(150, 100, 100, 255);
					applet.box(10.6f, 14.9f, 10.6f);
					applet.popMatrix();
					break;
				case 2:
					applet.pushMatrix();
					applet.translate(myPos[i].e[0], myPos[i].e[1], 0);
					applet.scale(myScale[i].e[0], myScale[i].e[1], myScale[i].e[2]);
					applet.rotateZ(myRot[i]);
					applet.translate(0, 0, 12.5f);
					applet.fill(140, 140, 140, 255);
					applet.box(60, 60, 140);
					applet.popMatrix();
					break;
				}
			}
		}
	}

} //end of class boxes
